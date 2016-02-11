<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function toOptionArray()
    {
        $methods = [];

        //adiciona um valor vazio caso nao queria excluir nada
        $methods[] = ["value" => "", "label" => ""];
        $access_token = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientId = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

//        $access_token = Mage::getStoreConfig();
//        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
//        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (empty($access_token) && (empty($clientId) || empty($clientSecret))) {
            return $methods;
        }

        //verifico se as credenciais não são vazias, caso sejam não é possível obte-los
        if (empty($access_token)) {
            $access_token = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret)->get_access_token();
        }

        Mage::helper('mercadopago')->log("Get payment methods by country... ", 'mercadopago.log');
        Mage::helper('mercadopago')->log("API payment methods: " . "/v1/payment_methods?access_token=" . $access_token, 'mercadopago.log');
        $response = MercadoPago_Lib_RestClient::get("/v1/payment_methods?access_token=" . $access_token);

        Mage::helper('mercadopago')->log("API payment methods", 'mercadopago.log', $response);

        $response = $response['response'];

        foreach ($response as $m) {
            if ($m['id'] != 'account_money') {
                $methods[] = array(
                    'value' => $m['id'],
                    'label' => Mage::helper('mercadopago')->__($m['name'])
                );
            }
        }

        return $methods;
        return [];
    }
}
