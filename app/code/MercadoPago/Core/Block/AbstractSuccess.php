<?php
namespace MercadoPago\Core\Block;


class AbstractSuccess
    extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \MercadoPago\Core\Model\Factory
     */
    protected $_coreFactory;

    protected $_orderFactory;

    protected $_checkoutSession;



    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MercadoPago\Core\Model\CoreFactory $coreFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->_coreFactory = $coreFactory;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getPayment()
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();

        return $payment;
    }

    public function getOrder()
    {
        $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        return $order;
    }

    public function getTotal()
    {
        $order = $this->getOrder();
        $total = $order->getBaseGrandTotal();

        if (!$total) {
            $total = $order->getBasePrice() + $order->getBaseShippingAmount();
        }

        $total = number_format($total, 2, '.', '');

        return $total;
    }

    public function getEntityId()
    {
        $order = $this->getOrder();

        return $order->getEntityId();
    }

    public function getPaymentMethod()
    {
        $payment_method = $this->getPayment()->getMethodInstance()->getCode();

        return $payment_method;
    }

    public function getInfoPayment()
    {
        $order_id = $this->_checkoutSession->getLastRealOrderId();
        $info_payments = $this->_coreFactory->create()->getInfoPaymentByOrder($order_id);

        return $info_payments;
    }

    public function getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment)
    {
        return $this->_coreFactory->create()->getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment);
    }

    public function getOrderUrl()
    {
        $params = ['order_id' => $this->_checkoutSession->getLastRealOrderId()];
        $url = $this->_urlBuilder->getUrl('sales/order/view',$params);

        return $url;
    }
}