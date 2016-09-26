<?php

namespace MercadoPago\Core\Plugin;

class OrderCancelPlugin
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \MercadoPago\Core\Helper\Data
     */
    protected $dataHelper;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \MercadoPago\Core\Helper\Data $dataHelper)
    {
        $this->messageManager = $context->getMessageManager();
        $this->dataHelper = $dataHelper;
    }

    public function aroundCancel (\Magento\Sales\Model\Order $order, \Closure $proceed) {
        $a = 0;
        $this->order = $order;
        $this->salesOrderBeforeCancel();
        $result = $proceed();
        return $result;
    }

    protected function salesOrderBeforeCancel () {

        if ($this->order->getExternalRequest()) {
            return;
        }
        $orderStatus = $this->order->getData('status');
        $orderPaymentStatus = $this->order->getPayment()->getData('additional_information')['status'];

        $paymentID = $this->order->getPayment()->getData('additional_information')['id'];
        $paymentMethod = $this->order->getPayment()->getMethodInstance()->getCode();

        $isValidBasicData = $this->checkCancelationBasicData ($paymentID, $paymentMethod);
        $isValidaData = $this->checkCancelationData ($orderStatus, $orderPaymentStatus);

        if ($isValidBasicData && $isValidaData) {
            $clientId = $this->dataHelper->getClientId();
            $clientSecret = $this->dataHelper->getClientSecret();

            $mp = $this->dataHelper->getApiInstance($clientId, $clientSecret);
            $response = null;

            $access_token = $this->dataHelper->getAccessToken();

            if ($paymentMethod == 'mercadopago_standard') {
                $response = $mp->cancel_payment($paymentID);
            } else {
                $data = [
                    "status" => 'cancelled',
                ];
                $response = $mp->put("/v1/payments/$paymentID?access_token=$access_token", $data);
            }

            if ($response['status'] == 200) {
                $this->messageManager->addSuccessMessage(__('Cancellation made by Mercado Pago'));
            } else {
                $this->messageManager->addErrorMessage(__('Failed to make the cancellation by Mercado Pago'));
                $this->messageManager->addErrorMessage($response['status'] . ' ' . $response['response']['message']);
                $this->throwCancelationException();
            }
        }
    }

    protected function checkCancelationBasicData ($paymentID, $paymentMethod) {

        if ($paymentID == null) {
            return false;
        }

        if (!($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom')) {
            $this->messageManager->addWarningMessage(__('Order payment wasn\'t made by Mercado Pago. The cancellation will be made through Magento'));
            return false;
        }

        $refundAvailable = $this->dataHelper->isRefundAvailable();
        if (!$refundAvailable) {
            $this->messageManager->addWarningMessage(__('Mercado Pago cancellation are disabled. The cancellation will be made through Magento'));
            return false;
        }

        return true;
    }

    protected function checkCancelationData ($orderStatus, $orderPaymentStatus) {
        $isValidaData = true;

        if (!($orderStatus == 'processing' || $orderStatus == 'pending')) {
            $this->messageManager->addErrorMessage(__('You can only make cancellations on orders whose status is Processing or Pending'));
            $isValidaData = false;
        }

        if (!($orderPaymentStatus == 'pending' || $orderPaymentStatus == 'in_process' || $orderPaymentStatus == 'rejected' )) {
            $this->messageManager->addErrorMessage(__('You can only make cancellations on orders whose payment status is Rejected, Pending o In Process'));
            $isValidaData = false;
        }

        if (!$isValidaData) {
            $this->throwCancelationException();
        }

        return $isValidaData;
    }

    protected function throwCancelationException () {
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Mercado Pago - Cancellations not made'));
    }
}