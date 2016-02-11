<?php

namespace MercadoPago\Core\Model\Standard;


class Payment
    extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'mercadopago_standard';

    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;


}