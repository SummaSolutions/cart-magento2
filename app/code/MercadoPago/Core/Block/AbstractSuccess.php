<?php
namespace MercadoPago\Core\Block;


class AbstractSuccess
    extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \MercadoPago\Core\Model\Factory
     */
    protected $coreFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MercadoPago\Core\Model\CoreFactory $coreFactory,
        array $data = []
    )
    {
        $this->coreFactory = $coreFactory;
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
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

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
        $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $info_payments = $this->coreFactory->create()->getInfoPaymentByOrder($order_id);

        return $info_payments;
    }

    public function getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment)
    {
        return $this->coreFactory->create()->getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment);
    }

    public function getOrderUrl()
    {
        //TODO: url lto $this->_getOrder()
        return '';
    }
}