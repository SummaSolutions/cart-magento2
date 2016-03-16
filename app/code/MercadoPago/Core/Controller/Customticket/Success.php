<?php
namespace MercadoPago\Core\Controller\Customticket;

/**
 * Class Success
 *
 * @package MercadoPago\Core\Controller\Customticket
 */
class Success
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {

        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;

        parent::__construct(
            $context
        );

    }

    public function execute()
    {
        $this->_view->loadLayout(['default', 'mercadopago_customticket_success']);

        $this->_view->renderLayout();
    }
}