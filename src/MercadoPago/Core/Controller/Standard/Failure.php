<?php
namespace MercadoPago\Core\Controller\Standard;

/**
 * Class Failure
 *
 * @package MercadoPago\Core\Controller\Standard
 */
class Failure
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {

        $this->_scopeConfig = $scopeConfig;

        parent::__construct(
            $context
        );

    }

    /**
     * Execute Failure action
     */
    public function execute()
    {
        $typeCheckout = $this->_scopeConfig->getValue('payment/mercadopago_standard/type_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->_view->loadLayout(['default', 'mercadopago_standard_failure_' . $typeCheckout]);

        $this->_view->renderLayout();
    }

}