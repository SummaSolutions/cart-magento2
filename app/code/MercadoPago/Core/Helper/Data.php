<?php
namespace MercadoPago\Core\Helper;
use Magento\Framework\View\LayoutFactory;


class Data
    extends \Magento\Payment\Helper\Data
{

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom/access_token';
    const XML_PATH_PUBLIC_KEY = 'payment/mercadopago_custom/public_key';
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';

    const PLATFORM_OPENPLATFORM = 'openplatform';
    const PLATFORM_STD = 'std';
    const TYPE = 'magento';

    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $coreHelper;

    /**
     * @var \MercadoPago\Core\Helper\Message\MessageInterface
     */
    protected $messageInterface;

    protected $coreHelperFactory;

    public function __construct(
        \MercadoPago\Core\Helper\Message\MessageInterface $messageInterface,
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->messageInterface = $messageInterface;
    }
    public function log($message, $file = "mercadopago.log", $array = null)
    {
        //pega a configuração de log no admin, essa variavel vem como true por padrão
        $action_log = $this->scopeConfig->getValue('payment/mercadopago/logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //caso tenha um array, transforma em json para melhor visualização
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        $this->_logger->info($message, []);
    }

    public function getApiInstance()
    {
        $params = func_num_args();
        if ($params > 2 || $params < 1) {
            throw new \Magento\Framework\Exception\LocalizedException("Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN");
        }
        if ($params == 1) {
            $api = new \MercadoPago_Lib_Api(func_get_arg(0));
            $api->set_platform(self::PLATFORM_OPENPLATFORM);
        } else {
            $api = new \MercadoPago_Lib_Api(func_get_arg(0), func_get_arg(1));
            $api->set_platform(self::PLATFORM_STD);
        }
        if ($this->scopeConfig->getValue('payment/mercadopago_standard/sandbox_mode')) {
            $api->sandbox_mode(true);
        }

        $api->set_type(self::TYPE);
        //$api->set_so((string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version); //TODO get module version

        return $api;

    }

    public function isValidAccessToken($accessToken)
    {
        $mp = $this->getApiInstance($accessToken);
        $response = $mp->get("/v1/payment_methods");
        if ($response['status'] == 401 || $response['status'] == 400) {
            return false;
        }

        return true;
    }

    public function isValidClientCredentials($clientId, $clientSecret)
    {
        $mp = $this->getApiInstance($clientId, $clientSecret);
        try {
            $mp->get_access_token();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function getAccessToken()
    {
        $clientId = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->getApiInstance($clientId, $clientSecret)->get_access_token();
    }

    public function getStatusOrder($status)
    {
        switch ($status) {
            case 'approved': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_approved', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'refunded': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_refunded', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'in_mediation': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_in_mediation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'cancelled': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_cancelled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'rejected': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_rejected', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'chargeback': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_chargeback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            default: {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_in_process', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
        }

        return $status;
    }

    /**
     * Get the assigned state of an order status
     *
     * @param string $status
     */
    public function _getAssignedState($status)
    {
        $item = Mage::getResourceModel('sales/order_status_collection')
            ->joinStates()
            ->addFieldToFilter('main_table.status', $status);

        return array_pop($item->getItems())->getState();
    }

    public function getMessage($status, $payment)
    {
        $rawMessage = __($this->messageInterface->getMessage($status));
        $rawMessage .= __('<br/> Payment id: %s', $payment['id']);
        $rawMessage .= __('<br/> Status: %s', $payment['status']);
        $rawMessage .= __('<br/> Status Detail: %s', $payment['status_detail']);

        return $rawMessage;
    }

    public function setOrderSubtotals($data, $order)
    {
        if (isset($data['total_paid_amount'])){
            $balance = $this->_getMultiCardValue($data['total_paid_amount']);
        } else {
            $balance = $data['transaction_details']['total_paid_amount'];
        }

        $order->setGrandTotal($balance);
        $order->setBaseGrandTotal($balance);

        $couponAmount = $this->_getMultiCardValue($data['coupon_amount']);
        $transactionAmount = $this->_getMultiCardValue($data['transaction_amount']);
        $shippingCost = $this->_getMultiCardValue($data['shipping_cost']);
        if ($couponAmount) {
            $order->setDiscountCouponAmount($couponAmount * -1);
            $order->setBaseDiscountCouponAmount($couponAmount * -1);
            $balance = $balance - ($transactionAmount - $couponAmount + $shippingCost);
        } else {
            $balance = $balance - $transactionAmount - $shippingCost;
        }

        if ($balance > 0) {
            $order->setFinanceCostAmount($balance);
            $order->setBaseFinanceCostAmount($balance);
        }
    }

    /**
     * @param $payment
     *
     * @return mixed
     */
    public function setPayerInfo(&$payment)
    {
        $payment["trunc_card"] = "xxxx xxxx xxxx " . $payment['card']["last_four_digits"];
        $payment["cardholder_name"] = $payment['card']["cardholder"]["name"];
        $payment['payer_first_name'] = $payment['payer']['first_name'];
        $payment['payer_last_name'] = $payment['payer']['last_name'];
        $payment['payer_email'] = $payment['payer']['email'];

        return $payment;
    }

    protected function _getMultiCardValue($fullValue) {
        $finalValue = 0;
        $values = explode('|', $fullValue);
        foreach ($values as $value) {
            $value = (float) str_replace(' ', '', $value);
            $finalValue = $finalValue + $value;
        }

        return $finalValue;
    }

}
