<?php
namespace MercadoPago\Core\Model;


class Core
    extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'mercadopago';

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_orderFactory;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MercadoPago\Core\Helper\Data $coreHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_storeManager = $storeManager;
        $this->_coreHelper = $coreHelper;
//        $this->scopeConfig = $scopeConfig;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get admin checkout session namespace
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getAdminCheckout()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieves Quote
     *
     * @param integer $quoteId
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote($quoteId = null)
    {
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        } else {
            if ($this->_storeManeger->getStore()->isAdmin()) {
                return $this->_getAdminCheckout()->getQuote();
            } else {
                return $this->_getCheckout()->getQuote();
            }
        }
    }

    /**
     * Retrieves Order
     *
     * @param integer $incrementId
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder($incrementId)
    {
        return $this->_orderFactory->create()->loadByIncrementId($incrementId);
    }

    public function getInfoPaymentByOrder($order_id)
    {
        $order = $this->_getOrder();
        $payment = $order->getPayment();
        $info_payments = array();
        $fields = array(
            array("field" => "cardholderName", "title" => "Card Holder Name: %s"),
            array("field" => "trunc_card", "title" => "Card Number: %s"),
            array("field" => "payment_method", "title" => "Payment Method: %s"),
            array("field" => "expiration_date", "title" => "Expiration Date: %s"),
            array("field" => "installments", "title" => "Installments: %s"),
            array("field" => "statement_descriptor", "title" => "Statement Descriptor: %s"),
            array("field" => "payment_id", "title" => "Payment id (MercadoPago): %s"),
            array("field" => "status", "title" => "Payment Status: %s"),
            array("field" => "status_detail", "title" => "Payment Detail: %s"),
            array("field" => "activation_uri", "title" => "Generate Ticket")
        );

        foreach ($fields as $field) {
            if ($payment->getAdditionalInformation($field['field']) != "") {
                $text = __($field['title'], $payment->getAdditionalInformation($field['field']));
                $info_payments[$field['field']] = array(
                    "text"  => $text,
                    "value" => $payment->getAdditionalInformation($field['field'])
                );
            }
        }

        return $info_payments;
    }

    protected function validStatusTwoPayments($status)
    {
        $array_status = explode(" | ", $status);
        $status_verif = true;
        $status_final = "";
        foreach ($array_status as $status) {

            if ($status_final == "") {
                $status_final = $status;
            } else {
                if ($status_final != $status) {
                    $status_verif = false;
                }
            }
        }

        if ($status_verif === false) {
            $status_final = "other";
        }

        return $status_final;
    }

    public function getMessageByStatus($status, $status_detail, $payment_method, $installment, $amount)
    {
        $status = $this->validStatusTwoPayments($status);
        $status_detail = $this->validStatusTwoPayments($status_detail);

        $message = array(
            "title"   => "",
            "message" => ""
        );

        $rawMessage = $this->_coreHelper->getMessage($status);
        $message['title'] = __($rawMessage['title']);

        if ($status == 'rejected') {
            if ($status_detail == 'cc_rejected_invalid_installments') {
                $message['message'] = __($this->_coreHelper->getMessage($status_detail), strtoupper($payment_method), $installment);
            } elseif ($status_detail == 'cc_rejected_call_for_authorize') {
                $message['message'] = __($this->_coreHelper->getMessage($status_detail), strtoupper($payment_method), $amount);
            } else {
                $message['message'] = __($this->_coreHelper->getMessage($status_detail), strtoupper($payment_method));
            }
        } else {
            $message['message'] = __($rawMessage['message']);
        }

        return $message;
    }

    protected function getCustomerInfo($customer, $order)
    {
        $email = htmlentities($customer->getEmail());
        if ($email == "") {
            $email = $order['customer_email'];
        }

        $first_name = htmlentities($customer->getFirstname());
        if ($first_name == "") {
            $first_name = $order->getBillingAddress()->getFirstname();
        }

        $last_name = htmlentities($customer->getLastname());
        if ($last_name == "") {
            $last_name = $order->getBillingAddress()->getLastname();
        }

        return array('email' => $email, 'first_name' => $first_name, 'last_name' => $last_name);
    }

    protected function getItemsInfo($order)
    {
        $dataItems = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = (string)Mage::helper('catalog/image')->init($product, 'image');

            $dataItems[] = array(
                "id"          => $item->getSku(),
                "title"       => $product->getName(),
                "description" => $product->getName(),
                "picture_url" => $image,
                "category_id" => $this->scopeConfig->getValue('payment/mercadopago/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "quantity"    => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price"  => (float)number_format($product->getPrice(), 2, '.', '')
            );
        }

        /* verify discount and add it like an item */
        $discount = $this->getDiscount();
        if ($discount != 0) {
            $dataItems[] = array(
                "title"       => "Discount by the Store",
                "description" => "Discount by the Store",
                "quantity"    => 1,
                "unit_price"  => (float)number_format($discount, 2, '.', '')
            );
        }

        return $dataItems;

    }

    protected function getCouponInfo($coupon, $coupon_code)
    {
        $infoCoupon = array();
        $infoCoupon['coupon_amount'] = (float)$coupon['response']['coupon_amount'];
        $infoCoupon['coupon_code'] = $coupon_code;
        $infoCoupon['campaign_id'] = $coupon['response']['id'];
        if ($coupon['status'] == 200) {
            $this->_coreHelper->log("Coupon applied. API response 200.", 'mercadopago-custom.log');
        } else {
            $this->_coreHelper->log("Coupon invalid, not applied.", 'mercadopago-custom.log');
        }

        return $infoCoupon;
    }

    public function makeDefaultPreferencePaymentV1($payment_info = array())
    {
        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->_getOrder($order_id);
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $billing_address = $quote->getBillingAddress()->getData();
        $customerInfo = $this->getCustomerInfo($customer, $order);

        /* INIT PREFERENCE */
        $preference = array();

        $preference['notification_url'] = Mage::getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_LINK) . "mercadopago/notifications/custom";
        $preference['transaction_amount'] = (float)$this->getAmount();
        $preference['external_reference'] = $order_id;
        $preference['payer']['email'] = $customerInfo['email'];

        if (!empty($payment_info['identification_type'])) {
            $preference['payer']['identification']['type'] = $payment_info['identification_type'];
            $preference['payer']['identification']['number'] = $payment_info['identification_number'];
        }
        $preference['additional_info']['items'] = $this->getItemsInfo($order);

        $preference['additional_info']['payer']['first_name'] = $customerInfo['first_name'];
        $preference['additional_info']['payer']['last_name'] = $customerInfo['last_name'];

        $preference['additional_info']['payer']['address'] = array(
            "zip_code"      => $billing_address['postcode'],
            "street_name"   => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => ''
        );

        $preference['additional_info']['payer']['registration_date'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());

        $shipping = $order->getShippingAddress()->getData();

        $preference['additional_info']['shipments']['receiver_address'] = array(
            "zip_code"      => $shipping['postcode'],
            "street_name"   => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
            "street_number" => '',
            "floor"         => "-",
            "apartment"     => "-",

        );

        $preference['additional_info']['payer']['phone'] = array(
            "area_code" => "0",
            "number"    => $shipping['telephone']
        );

        if (!empty($payment_info['coupon_code'])) {
            $coupon_code = $payment_info['coupon_code'];
            $this->_coreHelper->log("Validating coupon_code: " . $coupon_code, 'mercadopago-custom.log');

            $coupon = $this->validCoupon($coupon_code);
            $this->_coreHelper->log("Response API Coupon: ", 'mercadopago-custom.log', $coupon);

            $couponInfo = $this->getCouponInfo($coupon, $coupon_code);
            $preference['coupon_amount'] = $couponInfo['coupon_amount'];
            $preference['coupon_code'] = $couponInfo['coupon_code'];
            $preference['campaign_id'] = $couponInfo['campaign_id'];

        }

        $sponsor_id = $this->scopeConfig->getValue('payment/mercadopago/sponsor_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_coreHelper->log("Sponsor_id", 'mercadopago-standard.log', $sponsor_id);
        if (!empty($sponsor_id)) {
            $this->_coreHelper->log("Sponsor_id identificado", 'mercadopago-custom.log', $sponsor_id);
            $preference['sponsor_id'] = (int)$sponsor_id;
        }

        return $preference;
    }


    public function postPaymentV1($preference)
    {

        //obtem access_token
        $access_token = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_coreHelper->log("Access Token for Post", 'mercadopago-custom.log', $access_token);

        //seta sdk php mercadopago
        $mp = $this->_coreHelper->getApiInstance($access_token);
        $response = $mp->post("/v1/payments", $preference);
        $this->_coreHelper->log("POST /v1/payments", 'mercadopago-custom.log', $response);

        if ($response['status'] == 200 || $response['status'] == 201) {
            return $response;
        } else {
            $e = "";
            $exception = new \MercadoPago\Core\Model\Api\V1\Exception();
            if (count($response['response']['cause']) > 0) {
                foreach ($response['response']['cause'] as $error) {
                    $e .= $exception->getUserMessage($error) . " ";
                }
            } else {
                $e = $exception->getUserMessage();
            }

            $this->_coreHelper->log("erro post pago: " . $e, 'mercadopago-custom.log');
            $this->_coreHelper->log("response post pago: ", 'mercadopago-custom.log', $response);

            $exception->setMessage($e);
            throw $exception;
        }
    }

    public function getPayment($payment_id)
    {
        $clienId = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mp = $this->_coreHelper->getApiInstance($clienId, $clientSecret);

        return $mp->get_payment($payment_id);
    }

    public function getPaymentV1($payment_id)
    {
        $this->access_token = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mp = $this->_coreHelper->getApiInstance($this->access_token);

        return $mp->get("/v1/payments/" . $payment_id);
    }

    public function getMerchantOrder($merchant_order_id)
    {
        $clientId = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mp = $this->_coreHelper->getApiInstance($clientId, $clientSecret);

        return $mp->get("/merchant_orders/" . $merchant_order_id);
    }

    public function getPaymentMethods()
    {
        $this->access_token = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $mp = $this->_coreHelper->getApiInstance($this->access_token);

        $payment_methods = $mp->get("/v1/payment_methods");

        return $payment_methods;
    }

    public function getEmailCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $email = $customer->getEmail();

        if (empty($email)) {
            $quote = $this->_getQuote();
            $email = $quote->getBillingAddress()->getEmail();
        }

        return $email;
    }


    public function getAmount()
    {
        $quote = $this->_getQuote();
        $total = $quote->getBaseSubtotalWithDiscount() + $quote->getShippingAddress()->getShippingAmount();

        return (float)$total;

    }

    public function validCoupon($id)
    {
        $this->access_token = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $mp = $this->_coreHelper->getApiInstance($this->access_token);

        $params = array(
            "transaction_amount" => $this->getAmount(),
            "payer_email"        => $this->getEmailCustomer(),
            "coupon_code"        => $id
        );

        $details_discount = $mp->get("/discount_campaigns", $params);

        //add value on return api discount
        $details_discount['response']['transaction_amount'] = $params['transaction_amount'];
        $details_discount['response']['params'] = $params;


        return $details_discount;
    }

    public function setStatusOrder($payment, $stateObject = null)
    {
        $helper = $this->_coreHelper;
        $order = Mage::getModel('sales/order')->loadByIncrementId($payment["external_reference"]);
        $status = $payment['status'];

        if (isset($payment['status_final'])) {
            $status = $payment['status_final'];
        }
        $message = $helper->getMessage($status, $payment);

        try {
            if ($status == 'approved') {
                $this->_coreHelper->setOrderSubtotals($payment, $order);

                if (!$order->hasInvoices()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register()->pay();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();

                    $invoice->sendEmail(true, $message);
                }
                //Associate card to customer
                $additionalInfo = $order->getPayment()->getAdditionalInformation();
                if ($additionalInfo['token']) {
                    Mage::getModel('mercadopago/custom_payment')->customerAndCards($additionalInfo['token'], $payment);
                }


            } elseif ($status == 'refunded' || $status == 'cancelled') {
                $order->cancel();
            }

            $statusOrder = $helper->getStatusOrder($status);
            if ($stateObject) {
                $stateObject->setStatus($statusOrder);
                $stateObject->setState($helper->_getAssignedState($statusOrder));
                $stateObject->setIsNotified(true);
            }

            $order->setState($helper->_getAssignedState($statusOrder));
            $order->addStatusToHistory($statusOrder, $message, true);
            $order->sendOrderUpdateEmail(true, $message);

            $status_save = $order->save();
            $helper->log("Update order", 'mercadopago.log', $status_save->toString());
            $helper->log($message, 'mercadopago.log');

            return ['text' => $message, 'code' => \MercadoPago\Core\Helper\Response::HTTP_OK];
        } catch (Exception $e) {
            $helper->log("erro in set order status: " . $e, 'mercadopago.log');

            return ['text' => $e, 'code' => \MercadoPago\Core\Helper\Response::HTTP_BAD_REQUEST];
        }
    }

    public function updateOrder($data)
    {
        $this->_coreHelper->log("Update Order", 'mercadopago-notification.log');

        try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($data["external_reference"]);

            //update info de status no pagamento
            $payment_order = $order->getPayment();

            $additionalFields = array(
                'status',
                'status_detail',
                'payment_id',
                'transaction_amount',
                'cardholderName',
                'installments',
                'statement_descriptor',
                'trunc_card'

            );

            foreach ($additionalFields as $field) {
                if (isset($data[$field])) {
                    $payment_order->setAdditionalInformation($field, $data[$field]);
                }
            }

            if (isset($data['payment_method_id'])) {
                $payment_order->setAdditionalInformation('payment_method', $data['payment_method_id']);
            }

            $payment_status = $payment_order->save();
            $this->_coreHelper->log("Update Payment", 'mercadopago.log', $payment_status->toString());

            if ($data['payer_first_name']) {
                $order->setCustomerFirstname($data['payer_first_name']);
            }

            if ($data['payer_last_name']) {
                $order->setCustomerLastname($data['payer_last_name']);
            }

            if ($data['payer_email']) {
                $order->setCustomerEmail($data['payer_email']);
            }


            $status_save = $order->save();
            $this->_coreHelper->log("Update order", 'mercadopago.log', $status_save->toString());
        } catch (Exception $e) {
            $this->_coreHelper->log("erro in update order status: " . $e, 'mercadopago.log');
            $this->getResponse()->setBody($e);

            //caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
            $this->getResponse()->setHttpResponseCode(\MercadoPago\Core\Helper\Response::HTTP_BAD_REQUEST);
        }
    }

}
