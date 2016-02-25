<?php

namespace Mercadopago\Core\Controller\Standard;

/**
 * Class Pay
 *
 * @package Mercadopago\Core\Controller\Standard
 */
class Pay
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \MercadoPago\Core\Model\Standard\PaymentFactory
     */
    protected $_paymentFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MercadoPago\Core\Model\Standard\PaymentFactory $paymentFactory
    )
    {
        $this->_paymentFactory = $paymentFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $standard = $this->_paymentFactory->create();
        $array_assign = $standard->postPago();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($array_assign['init_point']);

        return $resultRedirect;
    }
}