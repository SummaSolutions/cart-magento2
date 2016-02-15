<?php

namespace MercadoPago\Core\Model\Standard;


class Payment
    extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'mercadopago_standard';
    const ACTION_URL = 'http://mercadopago2.local/mercadopago/standard/pay';

    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;

    protected $_helperData;
    protected $_helperImage;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_orderFactory;
    protected $_urlBuilder;

    public function __construct(
        \MercadoPago\Core\Helper\Data $helperData,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_helperData = $helperData;
        $this->_helperImage = $helperImage;

        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    public function postPago()
    {
        //seta sdk php mercadopago
        $client_id = $this->_scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $client_secret = $this->_scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $mp = $this->_helperData->getApiInstance($client_id, $client_secret);

        //monta a prefernecia
        $pref = $this->makePreference();
        $this->_helperData->log("make array", 'mercadopago-standard.log', $pref);

        //faz o posto do pagamento
        $response = $mp->create_preference($pref);
        $this->_helperData->log("create preference result", 'mercadopago-standard.log', $response);

        $array_assign = [];

        if ($response['status'] == 200 || $response['status'] == 201) {
            $payment = $response['response'];
            if ($this->_scopeConfig->getValue('payment/mercadopago_standard/sandbox_mode')) {
                $init_point = $payment['sandbox_init_point'];
            } else {
                $init_point = $payment['init_point'];
            }

            $array_assign = [
                "init_point"      => $init_point,
                "type_checkout"   => $this->getConfigData('type_checkout'),
                "iframe_width"    => $this->getConfigData('iframe_width'),
                "iframe_height"   => $this->getConfigData('iframe_height'),
                "banner_checkout" => $this->getConfigData('banner_checkout'),
                "status"          => 201
            ];

            $this->_helperData->log("Array preference ok", 'mercadopago-standard.log');
        } else {
            $array_assign = [
                "message" => __('An error has occurred. Please refresh the page.'),
                "json"    => json_encode($response),
                "status"  => 400
            ];

            $this->_helperData->log("Array preference error", 'mercadopago-standard.log');
        }

        return $array_assign;
    }

    public function makePreference()
    {
        $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        $customer = $this->_customerSession->getCustomer();
        $payment = $order->getPayment();
        $arr = [];

        $arr['external_reference'] = $orderIncrementId;
        $arr['items'] = $this->getItems($order);

        $this->_calculateDiscountAmount($arr['items'], $order);
        $this->_calculateBaseTaxAmount($arr['items'], $order);
        $total_item = $this->getTotalItems($arr['items']);
        $total_item += (float)$order->getBaseShippingAmount();

        $order_amount = (float)$order->getBaseGrandTotal();
        if (!$order_amount) {
            $order_amount = (float)$order->getBasePrice() + $order->getBaseShippingAmount();
        }

        if ($total_item > $order_amount || $total_item < $order_amount) {
            $diff_price = $order_amount - $total_item;
            $arr['items'][] = [
                "title"       => "Difference amount of the items with a total",
                "description" => "Difference amount of the items with a total",
                "category_id" => $this->_scopeConfig->getValue('payment/mercadopago/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "quantity"    => 1,
                "unit_price"  => (float)$diff_price
            ];
            $this->_helperData->log("Total itens: " . $total_item, 'mercadopago-standard.log');
            $this->_helperData->log("Total order: " . $order_amount, 'mercadopago-standard.log');
            $this->_helperData->log("Difference add itens: " . $diff_price, 'mercadopago-standard.log');
        }

        $shipping = $order->getShippingAddress()->getData();

        $arr['payer']['phone'] = [
            "area_code" => "-",
            "number"    => $shipping['telephone']
        ];

        $arr['shipments'] = $this->_getShipmentsParams($order);

        $billing_address = $order->getBillingAddress()->getData();

        $arr['payer']['date_created'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());
        $arr['payer']['email'] = htmlentities($customer->getEmail());
        $arr['payer']['first_name'] = htmlentities($customer->getFirstname());
        $arr['payer']['last_name'] = htmlentities($customer->getLastname());

        if (isset($payment['additional_information']['doc_number']) && $payment['additional_information']['doc_number'] != "") {
            $arr['payer']['identification'] = [
                "type"   => "CPF",
                "number" => $payment['additional_information']['doc_number']
            ];
        }

        $arr['payer']['address'] = [
            "zip_code"      => $billing_address['postcode'],
            "street_name"   => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => ""
        ];

        $arr['back_urls'] = [
            'success'=> $this->_urlBuilder->getUrl('mercadopago/success'),
            'pending'=> $this->_urlBuilder->getUrl('mercadopago/success'),
            'failure'=> $this->_urlBuilder->getUrl('mercadopago/success')
        ];

        $arr['notification_url'] = $this->_urlBuilder->getUrl("mercadopago/notifications/standard");

        $arr['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentsMethods();
        $installments = $this->getConfigData('installments');
        $arr['payment_methods']['installments'] = (int)$installments;

        $auto_return = $this->getConfigData('auto_return');
        if ($auto_return == 1) {
            $arr['auto_return'] = "approved";
        }

        $sponsor_id = $this->_scopeConfig->getValue('payment/mercadopago/sponsor_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_helperData->log("Sponsor_id", 'mercadopago-standard.log', $sponsor_id);
        if (!empty($sponsor_id)) {
            $this->_helperData->log("Sponsor_id identificado", 'mercadopago-standard.log', $sponsor_id);
            $arr['sponsor_id'] = (int)$sponsor_id;
        }


        return $arr;
    }

    protected function getItems($order)
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = $this->_helperImage->init($product, 'image');

            $items[] = [
                "id"          => $item->getSku(),
                "title"       => $product->getName(),
                "description" => $product->getName(),
                "picture_url" => $image->getUrl(),
                "category_id" => $this->_scopeConfig->getValue('payment/mercadopago/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "quantity"    => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price"  => (float)number_format($item->getPrice(), 2, '.', '')
            ];
        }

        return $items;
    }

    protected function _calculateDiscountAmount(&$arr, $order)
    {
        if ($order->getDiscountAmount() < 0) {
            $arr[] = [
                "title"       => "Store discount coupon",
                "description" => "Store discount coupon",
                "category_id" => $this->_scopeConfig->getValue('payment/mercadopago/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "quantity"    => 1,
                "unit_price"  => (float)$order->getDiscountAmount()
            ];
        }
    }

    protected function _calculateBaseTaxAmount(&$arr, $order)
    {
        if ($order->getBaseTaxAmount() > 0) {
            $arr[] = [
                "title"       => "Store taxes",
                "description" => "Store taxes",
                "category_id" => $this->_scopeConfig->getValue('payment/mercadopago/category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "quantity"    => 1,
                "unit_price"  => (float)$order->getBaseTaxAmount()
            ];
        }
    }

    protected function getTotalItems($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }

        return $total;
    }

    protected function getExcludedPaymentsMethods()
    {
        $excludedMethods = [];
        $excluded_payment_methods = $this->getConfigData('excluded_payment_methods');
        $arr_epm = explode(",", $excluded_payment_methods);
        if (count($arr_epm) > 0) {
            foreach ($arr_epm as $m) {
                $excludedMethods[] = ["id" => $m];
            }
        }

        return $excludedMethods;
    }

    protected function _getShipmentsParams($order)
    {
        $params = [];
        $shippingCost = $order->getBaseShippingAmount();
        $shippingAddress = $order->getShippingAddress();
        $method = $order->getShippingMethod();
        //TODO JOIN WITH MERCADOENVIOS
//        if ($this->mercadoEnviosHelper->isMercadoEnviosMethod($method)) {
//            $zipCode = $shippingAddress->getPostcode();
//            $defaultShippingId = substr($method, strpos($method, '_') + 1);
//            $params = [
//                'mode'                    => 'me2',
//                'zip_code'                => $zipCode,
//                'default_shipping_method' => intval($defaultShippingId),
//                'dimensions'              => $this->mercadoEnviosHelper->getDimensions($order->getAllItems())
//            ];
//            if ($shippingCost == 0) {
//                $params['free_methods'] = [['id' => intval($defaultShippingId)]];
//            }
//        }
        if (!empty($shippingCost)) {
            $params['cost'] = (float)$order->getBaseShippingAmount();
        }

        $params['receiver_address'] = [
            "floor"         => "-",
            "zip_code"      => $shippingAddress->getPostcode(),
            "street_name"   => $shippingAddress->getStreet()[0] . " - " . $shippingAddress->getCity() . " - " . $shippingAddress->getCountryId(),
            "apartment"     => "-",
            "street_number" => ""
        ];

        return $params;

    }

    public function getBannerCheckoutUrl() {
        return $this->getConfigData('banner_checkout');
    }

}