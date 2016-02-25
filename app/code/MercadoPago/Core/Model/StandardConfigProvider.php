<?php

namespace MercadoPago\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Return configs to Standard Method
 *
 * Class StandardConfigProvider
 *
 * @package MercadoPago\Core\Model
 */
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
                    'actionUrl' => $this->methodInstance->getActionUrl(),
                    'bannerUrl' => $this->methodInstance->getConfigData('banner_checkout'),
                    'type_checkout'  => $this->methodInstance->getConfigData('type_checkout')
                ],
            ],
        ] : [];
    }
}