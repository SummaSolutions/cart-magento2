<?php
namespace MercadoPago\Core\Controller\Success;

/**
 * Class Success
 *
 * @package MercadoPago\Core\Controller\Success
 */
class Page
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

    /**
     * Success constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory     $orderFactory
     */
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

    /**
     * Controller action
     */
    public function execute()
    {
        $checkoutTypeHandle = $this->getCheckoutHandle();
        $this->_view->loadLayout(['default', $checkoutTypeHandle]);

        $this->_view->renderLayout();
    }

    /**
     * Return handle name, depending on payment method used in the order placed
     *
     * @return string
     */
    public function getCheckoutHandle()
    {
        $handle = '';
        $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        if (!empty($order->getId())) {
            $handle = $order->getPayment()->getMethod();
        }
        $handle .= '_success';

        return $handle;
    }
}