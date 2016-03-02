<?php
namespace MercadoPago\Core\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ConfigObserver
 *
 * @package MercadoPago\Core\Observer
 */
class ConfigObserver
    implements ObserverInterface
{
    /**
     * url banners grouped by country
     *
     * @var array
     */
    private $banners = [
        "mercadopago_custom"       => [
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
        "mercadopago_standard"     => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG"
        ]
    ];

    /**
     * Available countries to custom checkout
     *
     * @var array
     */
    private $available_transparent_credit_cart = ['mla', 'mlb', 'mlm'];

    /**
     * Available countries to custom ticket
     *
     * @var array
     */
    private $available_transparent_ticket = ['mla', 'mlb', 'mlm'];

    /**
     *
     */
    const LOG_NAME = 'mercadopago';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $coreHelper;

    /**
     * Config configResource
     *
     * @var $configResource
     */
    protected $configResource;

    /**
     * ConfigObserver constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MercadoPago\Core\Helper\Data                      $coreHelper
     * @param \Magento\Config\Model\ResourceModel\Config         $configResource
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\Data $coreHelper,
        \Magento\Config\Model\ResourceModel\Config $configResource
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Updates configuration values based every time MercadoPago configuration section is saved
     * @param \Magento\Framework\Event\Observer $observer
     *
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

    /**
     * Disables custom checkout if selected country is not available
     */
    public function availableCheckout()
    {
        $country = $this->scopeConfig->getValue('payment/mercadopago/country');

        if (!in_array($country, $this->available_transparent_credit_cart)) {
            $this->configResource->saveConfig('payment/mercadopago_custom/active', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        }

        if (!in_array($country, $this->available_transparent_ticket)) {
            $this->configResource->saveConfig('payment/mercadopago_customticket/active', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        }
    }

    /**
     * Check if banner checkout img needs to be updated based on selected country
     * @param $type_checkout
     */
    public function checkBanner($type_checkout)
    {
        //get country
        $country = $this->scopeConfig->getValue('payment/mercadopago/country');
        if (!isset($this->banners[$type_checkout][$country])) {
            return;
        }
        $default_banner = $this->banners[$type_checkout][$country];

        $current_banner = $this->scopeConfig->getValue('payment/' . $type_checkout . '/banner_checkout');

        $this->coreHelper->log("Type Checkout Path: " . $type_checkout, self::LOG_NAME);
        $this->coreHelper->log("Current Banner: " . $current_banner, self::LOG_NAME);
        $this->coreHelper->log("Default Banner: " . $default_banner, self::LOG_NAME);

        if (in_array($current_banner, $this->banners[$type_checkout])) {
            $this->coreHelper->log("Banner default need update...", self::LOG_NAME);

            if ($default_banner != $current_banner) {
                $this->configResource->saveConfig('payment/' . $type_checkout . '/banner_checkout', $default_banner, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

                $this->coreHelper->log('payment/' . $type_checkout . '/banner_checkout setted ' . $default_banner, self::LOG_NAME);
            }
        }
    }


    /**
     * Set configuration value sponsor_id based on current credentials
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setSponsor()
    {
        $this->coreHelper->log("Sponsor_id: " . $this->scopeConfig->getValue('payment/mercadopago/sponsor_id'), self::LOG_NAME);

        $sponsor_id = "";
        $this->coreHelper->log("Valid user test", self::LOG_NAME);

        $access_token = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN);
        $this->coreHelper->log("Get access_token: " . $access_token, self::LOG_NAME);

        if (!$access_token) {
            return;
        }

        $mp = $this->coreHelper->getApiInstance($access_token);
        $user = $mp->get("/users/me");
        $this->coreHelper->log("API Users response", self::LOG_NAME, $user);

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

            $this->coreHelper->log("Sponsor id set", self::LOG_NAME, $sponsor_id);
        }

        $this->configResource->saveConfig('payment/mercadopago/sponsor_id', $sponsor_id, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->coreHelper->log("Sponsor saved", self::LOG_NAME, $sponsor_id);
    }

    /**
     * Validate current accessToken
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateAccessToken()
    {
        $accessToken = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN);
        if (!empty($accessToken)) {
            if (!$this->coreHelper->isValidAccessToken($accessToken)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MercadoPago - Custom Checkout: Invalid access token'));
            }
        }
    }

    /**
     * Validate current clientId and clientSecret
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateClientCredentials()
    {
        $clientId = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_ID);
        $clientSecret = $this->scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_CLIENT_SECRET);
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!$this->coreHelper->isValidClientCredentials($clientId, $clientSecret)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('MercadoPago - Classic Checkout: Invalid client id or client secret'));
            }
        }
    }
}
