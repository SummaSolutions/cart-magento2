<?php
namespace MercadoPago\Core\Model\System\Config\Source\Order;

/**
 * Overrides array to avoid showing certain statuses as an option
 * Class Status
 *
 * @package MercadoPago\Core\Model\System\Config\Source\Order
 */
class Status
    extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED,
    ];

}
