<?php

namespace MercadoPago\Core\Block\Calculator;


class CalculatorLink
    extends \Magento\Framework\View\Element\Template
{

    const PAGE_PDP = 'product.info.calculator';
    const PAGE_CART = 'checkout.cart.calculator';

    /**
     * @var $helperData \MercadoPago\Core\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_mysession;

    /**
     * CalculatorLink constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param \MercadoPago\Core\Helper\Data                      $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context    $context,
        \MercadoPago\Core\Helper\Data                       $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig,
        \Magento\Framework\Registry                         $registry,
        \Magento\Checkout\Model\Session                     $session,


        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperData = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_registry     = $registry;
        $this->_mysession = $session;
    }


    /**
     * Check if the access token is valid, if the API is not down and if the configuration is enabled
     *
     * @return bool
     */
    public function isAvailableCalculator(){

        $accessToken = $this->_scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN);
        return  ($this->_helperData->isValidAccessToken($accessToken)
                            & $this->_helperData->isAvailableCalculator());
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    public function isPageToShow($nameLayoutConteiner){

        $valueConfig = $this->_helperData->getPagesToShow();
        $pages = explode(',', $valueConfig);

        return in_array($nameLayoutConteiner, $pages);
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    public function inPagePDP($nameLayoutConteiner){

        return $nameLayoutConteiner === self::PAGE_PDP;
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    public function inPageCheckoutCart($nameLayoutConteiner){

        return $nameLayoutConteiner === self::PAGE_CART;
    }

    public function getUrlCalculatorPayment(){
        return $this->_storeManager->getStore()->getBaseUrl() . 'mercadopago/calculator/popup';
    }

    public function getCurrentProductPrice(){
        return $this->_registry->registry('current_product')->getFinalPrice();
    }

    public function getCheckoutCartGrandTotal(){
        return $this->_mysession->getQuote()->getGrandTotal();
    }


}