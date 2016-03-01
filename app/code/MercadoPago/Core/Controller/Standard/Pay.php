<?php

namespace Mercadopago\Core\Controller\Standard;

/**
 * Class Pay action controller to pay order with MP
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

    /**
     * Pay constructor.
     *
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \MercadoPago\Core\Model\Standard\PaymentFactory $paymentFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MercadoPago\Core\Model\Standard\PaymentFactory $paymentFactory
    )
    {
        $this->_paymentFactory = $paymentFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     * 
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {

        $standard = $this->_paymentFactory->create();
        $array_assign = $standard->postPago();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($array_assign['init_point']);

        return $resultRedirect;
    }
}