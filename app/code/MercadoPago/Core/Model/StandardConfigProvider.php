<?php

namespace MercadoPago\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class StandardConfigProvider
    implements ConfigProviderInterface
{
    protected $methodInstance;

    protected $methodCode = Standard\Payment::CODE;


    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper
    )
    {
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
    }

    public function getConfig()
    {
        return $this->methodInstance->isAvailable() ? [
            'payment' => [
                $this->methodCode => [
                    'actionUrl' => 'http://www.yahoo.com'
                ],
            ],
        ] : [];
    }
}