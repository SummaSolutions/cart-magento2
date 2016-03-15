<?php
namespace MercadoPago\Core\Model\Custom;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\Online\GatewayInterface;
use Magento\Payment\Model\Method\ConfigInterface;

/**
 * Class Payment
 *
 * @package MercadoPago\Core\Model\Custom
 */
class Payment
    extends \Magento\Payment\Model\Method\Cc
    implements GatewayInterface
{
    /**
     * Define payment method code
     */
    const CODE = 'mercadopago_custom';

    protected $_code = self::CODE;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isProxy = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * {inheritdoc}
     */
    public function postRequest(DataObject $request, ConfigInterface $config)
    {
        return '';
    }

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var \MercadoPago\Core\Model\Core
     */
    protected $_coreModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $_helperData;

    const LOG_NAME = 'custom_payment';

    public function __construct(
        \MercadoPago\Core\Helper\Data $helperData,
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
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\MercadoPago\Core\Model\Core $coreModel)
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate
        );

        $this->_helperData = $helperData;
        $this->_coreModel = $coreModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_urlBuilder = $urlBuilder;

    }
    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {

        if ($this->getInfoInstance()->getAdditionalInformation('token') == "") {
            throw new \Exception(__('Verify the form data or wait until the validation of the payment data'));
        }

        $response = $this->preparePostPayment();

        if ($response !== false):

            $payment = $response['response'];
            $this->_helperData->log("Payment response", self::LOG_NAME, $payment);
            //set status
            $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
            $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);

            return true;
        endif;

        return false;
    }

    public function validate()
    {
        \Magento\Payment\Model\Method\AbstractMethod::validate();

        return $this;
    }


//    public function assignData(\Magento\Framework\DataObject $data)
//    {
//        parent::assignData($data);
//        $infoInstance = $this->getInfoInstance();
//        if ($this->getConfigData('fraudprotection') > 0) {
//            $infoInstance->setAdditionalInformation('device_data', $data->getData('device_data'));
//        }
//        $infoInstance->setAdditionalInformation('cc_last4', $data->getData('cc_last4'));
//        $infoInstance->setAdditionalInformation('cc_token', $data->getCcToken());
//        $infoInstance->setAdditionalInformation('payment_method_nonce', $data->getPaymentMethodNonce());
//        $infoInstance->setAdditionalInformation('store_in_vault', $data->getStoreInVault());
//        return $this;
//    }

    /**
     * Assign corresponding data
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        // route /checkout/onepage/savePayment
        if (!($data instanceof \Magento\Framework\DataObject)) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $info_form = $data->getData();
        //$info_form = $info_form['mercadopago_custom'];
        if (isset($info_form['one_click_pay']) && $info_form['one_click_pay'] == 1) {
            $info_form = $this->cleanFieldsOcp($info_form);
        }

        if (empty($info_form['token'])) {
            $exception = new MercadoPago\Core\Model\Api\V1\Exception();
            $exception->setMessage($exception->getUserMessage());
            throw $exception;
        }

        $this->_helperData->log("info form", self::LOG_NAME, $info_form);
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation($info_form);
        $info->setAdditionalInformation('payment_type_id', "credit_card");
        if (!empty($info_form['card_expiration_month']) && !empty($info_form['card_expiration_year'])) {
            $info->setAdditionalInformation('expiration_date', $info_form['card_expiration_month'] . "/" . $info_form['card_expiration_year']);
        }
        $info->setAdditionalInformation('payment_method', $info_form['payment_method_id']);
        $info->setAdditionalInformation('cardholderName', $info_form['card_holder_name']);

        return $this;
    }

    public function preparePostPayment()
    {
        $this->_helperData->log("Credit Card -> init prepare post payment", self::LOG_NAME);

        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->getInfoInstance()->getOrder();

        $payment = $order->getPayment();
        $payment_info = $this->getPaymentInfo($payment);

        $preference = $this->_coreModel->makeDefaultPreferencePaymentV1($payment_info, $quote, $order);

        $preference['installments'] = (int)$payment->getAdditionalInformation("installments");
        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");
        $preference['token'] = $payment->getAdditionalInformation("token");

        if ($payment->getAdditionalInformation("issuer_id") != "") {
            $preference['issuer_id'] = (int)$payment->getAdditionalInformation("issuer_id");
        }

        if ($payment->getAdditionalInformation("customer_id") != "") {
            $preference['payer']['id'] = $payment->getAdditionalInformation("customer_id");
        }

        $preference['binary_mode'] = $this->_scopeConfig->isSetFlag('payment/mercadopago_custom/binary_mode');
        $preference['statement_descriptor'] = $this->_scopeConfig->getValue('payment/mercadopago_custom/statement_descriptor');

        $this->_helperData->log("Credit Card -> PREFERENCE to POST /v1/payments", self::LOG_NAME, $preference);

        /* POST /v1/payments */
        $response = $this->_coreModel->postPaymentV1($preference);

        return $response;
    }


    /**
     * Retrieves quote
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }


    /**
     * @param $incrementId
     *
     * @return mixed
     */
    protected function _getOrder($incrementId)
    {
        return $this->_orderFactory->create()->loadByIncrementId($incrementId);
    }

    public function getOrderPlaceRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('mercadopago/custom/success', ['_secure' => true]);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $parent = parent::isAvailable($quote);
        $accessToken = $this->_scopeConfig->getValue(\MercadoPago\Core\Model\Core::XML_PATH_ACCESS_TOKEN);
        $publicKey = $this->_scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_PUBLIC_KEY);
        $custom = (!empty($publicKey) && !empty($accessToken));
        if (!$parent || !$custom) {
            return false;
        }
        return $this->_helperData->isValidAccessToken($accessToken);
    }

    /**
     * Get stored customers and cards from api
     * @return mixed
     */
    public function getCustomerAndCards()
    {
        $email = $this->_coreModel->getEmailCustomer();

        $customer = $this->getOrCreateCustomer($email);

        return $customer;
    }

    public function getOrCreateCustomer($email)
    {
        if (empty($email)){
            return false;
        }
        $access_token = $this->_scopeConfig->getValue(\MercadoPago\Core\Model\Core::XML_PATH_ACCESS_TOKEN);

        $mp = $this->_helperData->getApiInstance($access_token);

        $customer = $mp->get("/v1/customers/search", ["email" => $email]);

        $this->_helperData->log("Response search customer", self::LOG_NAME, $customer);

        if ($customer['status'] == 200) {

            if ($customer['response']['paging']['total'] > 0) {
                return $customer['response']['results'][0];
            } else {
                $this->_helperData->log("Customer not found: " . $email, self::LOG_NAME);

                $customer = $mp->post("/v1/customers", ["email" => $email]);

                $this->_helperData->log("Response create customer", self::LOG_NAME, $customer);

                if ($customer['status'] == 201) {
                    return $customer['response'];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @param $info
     *
     * @return mixed
     */
    protected function cleanFieldsOcp($info)
    {
        foreach (self::$_excludeInputsOpc as $field) {
            $info[$field] = '';
        }

        return $info;
    }

    protected function getPaymentInfo($payment)
    {
        $payment_info = [];

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $payment_info['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        if ($payment->getAdditionalInformation("doc_number") != "") {
            $payment_info['identification_type'] = $payment->getAdditionalInformation("doc_type");
            $payment_info['identification_number'] = $payment->getAdditionalInformation("doc_number");
        }

        return $payment_info;
    }

}
