<?php
namespace MercadoPago\Core\Block\Calculator;

class CalculatorForm
    extends \Magento\Framework\View\Element\Template
{

    const CALCULATOR_JS = 'mercadopago/mercadopago_calculator.js';

    /**
     * @var $helperData \MercadoPago\Core\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * CalculatorForm constructor.
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

        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperData = $helper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Return the PublicKey from MercadoPago checkout custom configuration
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        $key = $this->_helperData->getPublicKey();
        return $key;
    }

    /**
     * return the Payment methods token configured
     *
     * @return string
     */
    public function getPaymentMethods()
    {
        $accessToken = $this->_scopeConfig->getValue(\MercadoPago\Core\Helper\Data::XML_PATH_ACCESS_TOKEN);
        return $this->_helperData->getMercadoPagoPaymentMethods($accessToken);
    }

    /**
     * return the current value of amount
     *
     * @return mixed|bool
     */
    public function getAmount()
    {
        return $this->getRequest()->getParam('currentAmount');
    }

}