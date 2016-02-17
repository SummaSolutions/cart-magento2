<?php
namespace MercadoPago\Core\Logger\Handler;

use Monolog\Logger;

class System
    extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/mercadopago.log';

}