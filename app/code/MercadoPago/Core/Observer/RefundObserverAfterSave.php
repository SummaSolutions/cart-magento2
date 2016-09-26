<?php

namespace MercadoPago\Core\Observer;

use Magento\Framework\Event\ObserverInterface;

class RefundObserverAfterSave implements ObserverInterface
{
    protected $dataHelper;

    public function __construct(\MercadoPago\Core\Helper\Data $dataHelper) {
        $this->dataHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->creditMemoRefundAfterSave($observer);
    }

    protected function creditMemoRefundAfterSave (\Magento\Framework\Event\Observer $observer) {
        /**
         * @var $order \Magento\Sales\Model\Order
         * @var $creditMemo \Magento\Sales\Model\Order\Creditmemo
         */
        $creditMemo = $observer->getData('creditmemo');
        $status = $this->dataHelper->getOrderStatusRefunded();
        $order = $creditMemo->getOrder();
        $message = ($order->getExternalRequest()!= null ? 'From Mercado Pago' : 'From Store');
        if ($order->getMercadoPagoRefund() || $order->getExternalRequest()) {
            if ($order->getState() != $status) {
                $order->setState($status)
                    ->setStatus($order->getConfig()->getStateDefaultStatus($status))
                    ->addStatusHistoryComment('Partially Refunded ' . $message);
                $notificationData ["external_reference"] = $order->getIncrementId();
                $notificationData ["status"] = $status;
                $this->dataHelper->setStatusUpdated($notificationData);
            }
        }
    }
}