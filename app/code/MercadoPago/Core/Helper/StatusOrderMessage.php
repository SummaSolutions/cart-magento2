<?php
namespace MercadoPago\Core\Helper;

/**
 * Class StatusOrderMessage
 *
 * @package MercadoPago\Core\Helper
 */
class StatusOrderMessage
    extends \MercadoPago\Core\Helper\Message\AbstractMessage
{
    /**
     * mapping between status and message
     * @var array
     */
    protected $messagesMap = [
            "approved"     => 'Automatic notification of the Mercado Pago: The payment was approved.',
            "refunded"     => 'Automatic notification of the Mercado Pago: The payment was refunded.',
            "pending"      => 'Automatic notification of the Mercado Pago: The payment is being processed.',
            "in_process"   => 'Automatic notification of the Mercado Pago: The payment is being processed. Will be approved within 2 business days.',
            "in_mediation" => 'Automatic notification of the Mercado Pago: The payment is in the process of Dispute, check the graphic account of the Mercado Pago for more information.',
            "cancelled"    => 'Automatic notification of the Mercado Pago: The payment was cancelled.',
            "rejected"     => 'Automatic notification of the Mercado Pago: The payment was rejected.',
            "chargeback"   => 'Automatic notification of the Mercado Pago: One chargeback was initiated for this payment.',
    ];

    /**
     * Return self message map array
     * @return array
     */
    public function getMessageMap()
    {
        return $this->messagesMap;
    }
}