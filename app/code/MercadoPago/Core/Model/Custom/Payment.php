<?php
namespace MercadoPago\Core\Model\Custom;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\Online\GatewayInterface;
use Magento\Payment\Model\Method\ConfigInterface;

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

    protected $_coreHelper;
    const LOG_NAME = 'custom_payment';

//    public function __construct(
//        \Magento\Framework\Model\Context $context,
//        \Magento\Framework\Registry $registry,
//        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
//        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
//        \Magento\Payment\Helper\Data $paymentData,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
//        \Magento\Payment\Model\Method\Logger $logger,
//        \Magento\Framework\Module\ModuleListInterface $moduleList,
//        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
//        \Magento\Framework\Model\ResourceModel\AbstractResource $resource,
//        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection,
//        array $data,
//        \MercadoPago\Core\Helper\Data $coreHelper)
//    {
//        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, $resource, $resourceCollection, $data);
//        $this->_coreHelper = $coreHelper;
//
//    }

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
            Mage::throwException(Mage::helper('mercadopago')->__('Verify the form data or wait until the validation of the payment data'));
        }

        $response = $this->preparePostPayment();

        if ($response !== false):

            $payment = $response['response'];
            //set status
            $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
            $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);

            if ($response['status'] == 200 || $response['status'] == 201) {
                Mage::helper('mercadopago')->log("Received Payment data", self::LOG_FILE, $payment);

                $payment = Mage::helper('mercadopago')->setPayerInfo($payment);
                $core = Mage::getModel('mercadopago/core');
                Mage::helper('mercadopago')->log("Update Order", self::LOG_FILE);
                $core->updateOrder($payment);
                $core->setStatusOrder($payment, $stateObject);
            }

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
            $exception = new MercadoPago_Core_Model_Api_V1_Exception();
            $exception->setMessage($exception->getUserMessage());
            throw $exception;
        }

        $this->_coreHelper->log("info form", self::LOG_NAME, $info_form);
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


}
