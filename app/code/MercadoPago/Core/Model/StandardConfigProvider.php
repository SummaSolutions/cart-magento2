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
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstance;

    /**
     * @var string
     */
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

    /**
     * Return standard configs
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        if ($this->methodInstance->isAvailable()) {
            $config = [
                'payment' => [
                    $this->methodCode => [
                        'actionUrl'     => $this->methodInstance->getActionUrl(),
                        'bannerUrl'     => $this->methodInstance->getConfigData('banner_checkout'),
                        'type_checkout' => $this->methodInstance->getConfigData('type_checkout'),
                    ],
                ],
            ];
            if ($this->methodInstance->getConfigData('type_checkout') == 'iframe') {
                $config['payment'][$this->methodCode]['iframe_height'] = $this->methodInstance->getConfigData('iframe_height');
            }
        }
        return $config;
    }
}