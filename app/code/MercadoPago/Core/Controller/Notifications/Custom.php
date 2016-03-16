<?php
namespace Mercadopago\Core\Controller\Notifications;


/**
 * Class Custom
 *
 * @package Mercadopago\Core\Controller\Notifications
 */
class Custom
    extends \Magento\Framework\App\Action\Action

{
    protected $_paymentFactory;

    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $coreHelper;

    /**
     * @var \MercadoPago\Core\Model\Core
     */
    protected $coreModel;

    const LOG_NAME = 'custom_notification';


    /**
     * Standard constructor.
     *
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \MercadoPago\Core\Model\Standard\PaymentFactory $paymentFactory
     * @param \MercadoPago\Core\Helper\Data                   $coreHelper
     * @param \MercadoPago\Core\Model\Core                    $coreModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MercadoPago\Core\Model\Standard\PaymentFactory $paymentFactory,
        \MercadoPago\Core\Helper\Data $coreHelper,
        \MercadoPago\Core\Model\Core $coreModel
    )
    {
        $this->_paymentFactory = $paymentFactory;
        $this->coreHelper = $coreHelper;
        $this->coreModel = $coreModel;
        parent::__construct($context);
    }

    /**
     * Controller Action
     */
    public function execute()
    {
        $request = $this->getRequest();
        $this->coreHelper->log("Custom Received notification", self::LOG_NAME, $request->getParams());

        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $this->coreModel->getPaymentV1($dataId);
            $this->coreHelper->log("Return payment", self::LOG_NAME, $response);

            if ($response['status'] == 200 || $response['status'] == 201) {
                $payment = $response['response'];

                $payment = $this->coreHelper->setPayerInfo($payment);

                $this->coreHelper->log("Update Order", self::LOG_NAME);
                $this->coreModel->updateOrder($payment);
                $setStatusResponse = $this->coreModel->setStatusOrder($payment);
                $this->getResponse()->setBody($setStatusResponse['text']);
                $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
                $this->coreHelper->log("Http code", self::LOG_NAME, $this->getResponse()->getHttpResponseCode());
                return;
            }
        }

        $this->coreHelper->log("Payment not found", self::LOG_NAME, $request->getParams());
        $this->getResponse()->getBody("Payment not found");
        $this->getResponse()->setHttpResponseCode(\MercadoPago\Core\Helper\Response::HTTP_NOT_FOUND);
        $this->coreHelper->log("Http code", self::LOG_NAME, $this->getResponse()->getHttpResponseCode());
    }

}