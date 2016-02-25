<?php
namespace MercadoPago\Core\Model\System\Config\Source;

/**
 * Class PaymentMethods
 *
 * @package MercadoPago\Core\Model\System\Config\Source
 */
class PaymentMethods
    implements \Magento\Framework\Option\ArrayInterface
{
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom/access_token';
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';
    private $scopeConfig;

    protected $coreHelper;


    /**
     * PaymentMethods constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MercadoPago\Core\Helper\Data                      $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\Data $coreHelper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Return available payment methods
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];

        //default empty value
        $methods[] = ["value" => "", "label" => ""];
        $accessToken = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientId = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($accessToken) && (empty($clientId) || empty($clientSecret))) {
            return $methods;
        }

        $meHelper = $this->coreHelper;
        //if accessToken is empty uses clientId and clientSecret to obtain it
        if (empty($accessToken)) {
            $accessToken = $meHelper->getApiInstance($clientId, $clientSecret)->get_access_token();
        }

        $meHelper->log("Get payment methods by country... ", 'mercadopago');
        $meHelper->log("API payment methods: " . "/v1/payment_methods?access_token=" . $accessToken, 'mercadopago');
        $response = \MercadoPago_Lib_RestClient::get("/v1/payment_methods?access_token=" . $accessToken);

        $meHelper->log("API payment methods", 'mercadopago', $response);

        $response = $response['response'];

        foreach ($response as $m) {
            if ($m['id'] != 'account_money') {
                $methods[] = [
                    'value' => $m['id'],
                    'label' => __($m['name'])
                ];
            }
        }

        return $methods;
    }
}
