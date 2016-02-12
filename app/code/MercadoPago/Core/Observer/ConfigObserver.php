<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL).
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @category   	Payment Gateway
* @package    	MercadoPago
* @author      	Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
* @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
* @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
namespace MercadoPago\Core\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConfigObserver implements ObserverInterface
{
    private $banners = [
        "mercadopago_custom" => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG"
        ],
        "mercadopago_customticket" => [
            "mla" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/2014/230x60.png",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png"
        ],
        "mercadopago_standard" => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg"
        ]
    ];
    
    private $available_transparent_credit_cart =  ['mla', 'mlb', 'mlm'];
    private $available_transparent_ticket = ['mla', 'mlb', 'mlm'];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $coreHelperFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\DataFactory $coreHelperFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreHelperFactory = $coreHelperFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->validateAccessToken();

        $this->validateClientCredentials();

        $this->setSponsor();

        $this->availableCheckout();

        $this->checkBanner('mercadopago_custom');
        $this->checkBanner('mercadopago_customticket');
        $this->checkBanner('mercadopago_standard');

    }

    public function availableCheckout()
    {
        //verifica se o pais selecionado possui integracao para utilizar os checkouts transparents

        $country = $this->scopeConfig->getValue('payment/mercadopago/country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if (!in_array($country, $this->available_transparent_credit_cart)) {
            Mage::getConfig()->saveConfig('payment/mercadopago_custom/active', 0);
        }
        
        if (!in_array($country, $this->available_transparent_ticket)) {
            Mage::getConfig()->saveConfig('payment/mercadopago_customticket/active', 0);
        }
    }
    
    public function checkBanner($type_checkout)
    {
        //get country
        $country = $this->scopeConfig->getValue('payment/mercadopago/country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!isset($this->banners[$type_checkout][$country])){
            return;
        }
        $default_banner = $this->banners[$type_checkout][$country];
        
        $current_banner = $this->scopeConfig->getValue('payment/' . $type_checkout . '/banner_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $this->coreHelper->log("Type Checkout Path: " . $type_checkout, 'mercadopago.log');
        $this->coreHelper->log("Current Banner: " . $current_banner, 'mercadopago.log');
        $this->coreHelper->log("Default Banner: " . $default_banner, 'mercadopago.log');
        
        if (in_array($current_banner, $this->banners[$type_checkout])) {
            $this->coreHelper->log("Banner default need update...", 'mercadopago.log');
            
            if ($default_banner != $current_banner) {
                Mage::getConfig()->saveConfig('payment/' . $type_checkout . '/banner_checkout', $default_banner);
                
                $this->coreHelper->log('payment/' . $type_checkout . '/banner_checkout setted ' . $default_banner, 'mercadopago.log');
            }
        }
    }
    
    
    public function setSponsor()
    {
        $this->coreHelper->log("Sponsor_id: " . $this->scopeConfig->getValue('payment/mercadopago/sponsor_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 'mercadopago.log');
        
        $sponsor_id = "";
        $this->coreHelper->log("Valid user test", 'mercadopago.log');
        
        $access_token = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->coreHelper->log("Get access_token: " . $access_token, 'mercadopago.log');
        
        $mp = $this->coreHelper->getApiInstance($access_token);
        $user = $mp->get("/users/me");
        $this->coreHelper->log("API Users response", 'mercadopago.log', $user);
        
        if ($user['status'] == 200 && !in_array("test_user", $user['response']['tags'])) {

            switch ($user['response']['site_id']) {
                case 'MLA':
                    $sponsor_id = 186172525;
                    break;
                case 'MLB':
                    $sponsor_id = 186175129;
                    break;
                case 'MLM':
                    $sponsor_id = 186175064;
                    break;
                default:
                    $sponsor_id = "";
                    break;
            }
            
            $this->coreHelper->log("Sponsor id setted", 'mercadopago.log', $sponsor_id);
        }
        
        Mage::getConfig()->saveConfig('payment/mercadopago/sponsor_id',$sponsor_id);
        $this->coreHelper->log("Sponsor saved", 'mercadopago.log', $sponsor_id);
    }

    protected function validateAccessToken() {
        $accessToken = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($accessToken)) {
            if (!$this->coreHelper->isValidAccessToken($accessToken)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Mercado Pago - Custom Checkout: Invalid access token'));
            }
        }
    }

    protected function validateClientCredentials() {
        $clientId = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($clientId) &&  !empty($clientSecret)) {
            if (!$this->coreHelper->isValidClientCredentials($clientId,$clientSecret)){
                throw new \Magento\Framework\Exception\LocalizedException(__('Mercado Pago - Classic Checkout: Invalid client id or client secret'));
            }
        }
    }
}
