<?php
namespace MercadoPago\Core\Controller\Standard;

class Success
    extends \Magento\Framework\App\Action\Action
{

    protected $_checkoutSession;
    protected $_orderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {

        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;

        parent::__construct(
            $context
        );

    }

    public function execute()
    {
        $checkoutTypeHandle = $this->getCheckoutHandle();
        $this->_view->loadLayout(['default', $checkoutTypeHandle]);

//        $this->_initLayoutMessages('core/session');

        $this->_view->renderLayout();
    }

    public function getCheckoutHandle()
    {
        $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        $handle = $order->getPayment()->getMethod();
        $handle .= '_success';

        return $handle;
    }
}