<?php
namespace MercadoPago\Core\Helper;

/**
 * Class StatusUpdate
 *
 * @package MercadoPago\Core\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusUpdate
    extends \Magento\Payment\Helper\Data
{
    /**
     * @var bool flag indicates when status was updated by notifications.
     */
    protected $_statusUpdatedFlag = false;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \MercadoPago\Core\Helper\Message\MessageInterface
     */
    protected $_messageInterface;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Status\Collection
     */
    protected $_statusFactory;

    public function __construct(
        \MercadoPago\Core\Helper\Message\MessageInterface $messageInterface,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Sales\Model\ResourceModel\Status\Collection $statusFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->_messageInterface = $messageInterface;
        $this->_orderFactory = $orderFactory;
        $this->_statusFactory = $statusFactory;
    }

    /**
     * @return bool return updated flag
     */
    public function isStatusUpdated()
    {
        return $this->_statusUpdatedFlag;
    }

    /**
     * @return mixed
     */
    public function getOrderStatusRefunded() {
        return $this->scopeConfig->getValue('payment/mercadopago/order_status_refunded');
    }

    /**
     * Set flag status updated
     *
     * @param $notificationData
     */
    public function setStatusUpdated($notificationData)
    {
        /**
         * $order \Magento\Sales\Model\Order
         */
        $order = $this->_orderFactory->create()->loadByIncrementId($notificationData["external_reference"]);
        $status = $notificationData['status'];
        $currentStatus = $order->getPayment()->getAdditionalInformation('status');
        if (($status == $currentStatus)) {
            $this->_statusUpdatedFlag = true;
        }
    }

    /**
     * Return order status mapping based on current configuration
     *
     * @param $status
     *
     * @return mixed
     */
    public function getStatusOrder($status, $statusDetail, $isCanCreditMemo)
    {
        switch ($status) {
            case 'approved': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_approved', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($statusDetail == 'partially_refunded' && $isCanCreditMemo) {
                  $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_partially_refunded', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                }
                break;
            }
            case 'refunded': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_refunded', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'in_mediation': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_in_mediation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'cancelled': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_cancelled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'rejected': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_rejected', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            case 'chargeback': {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_chargeback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            }
            default: {
                $status = $this->scopeConfig->getValue('payment/mercadopago/order_status_in_process', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
        }

        return $status;
    }

    /**
     * Get the assigned state of an order status
     *
     * @param string $status
     */
    public function _getAssignedState($status)
    {
        $collection = $this->_statusFactory
            ->joinStates()
            ->addFieldToFilter('main_table.status', $status);

        $collectionItems = $collection->getItems();

        return array_pop($collectionItems)->getState();
    }

    /**
     * Return raw message for payment detail
     *
     * @param $status
     * @param $payment
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getMessage($status, $payment)
    {
        $rawMessage = __($this->_messageInterface->getMessage($status));
        $rawMessage .= __('<br/> Payment id: %1', $payment['id']);
        $rawMessage .= __('<br/> Status: %1', $payment['status']);
        $rawMessage .= __('<br/> Status Detail: %1', $payment['status_detail']);

        return $rawMessage;
    }

}