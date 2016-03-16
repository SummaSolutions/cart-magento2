<?php
namespace Mercadopago\Core\Controller\Api;


/**
 * Class Standard
 *
 * @package Mercadopago\Core\Controller\Notifications
 */
class Coupon
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
        $coupon_id = $this->getRequest()->getParam('id');
        if (!empty($coupon_id)) {
            $response = $this->coreModel->validCoupon($coupon_id);
        } else {
            $response = array(
                "status"   => 400,
                "response" => array(
                    "error"   => "invalid_id",
                    "message" => "invalid id"
                )
            );
        }

        $jsonData = json_encode($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}