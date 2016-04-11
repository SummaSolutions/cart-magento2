<?php
namespace MercadoPago\Core\Controller\Api;


/**
 * Class Coupon
 *
 * @package Mercadopago\Core\Controller\Notifications
 */
class Coupon
    extends \Magento\Framework\App\Action\Action

{
    /**
     * @var \MercadoPago\Core\Helper\
     */
    protected $coreHelper;

    /**
     * @var \MercadoPago\Core\Model\Core
     */
    protected $coreModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * Coupon constructor.
     *
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \MercadoPago\Core\Helper\Data              $coreHelper
     * @param \MercadoPago\Core\Model\Core               $coreModel
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MercadoPago\Core\Helper\Data $coreHelper,
        \MercadoPago\Core\Model\Core $coreModel,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context);
        $this->coreHelper = $coreHelper;
        $this->coreModel = $coreModel;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->_registry     = $registry;
    }

    /**
     * Fetch coupon info
     *
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
                    "message" => "invalid id",
                    "coupon_amount" => 0
                )
            );
        }
        //save value to DiscountCoupon collect
        $this->_registry->register('mercadopago_discount_amount', $response['response']['coupon_amount']);
        $quote = $this->_checkoutSession->getQuote();
        $this->quoteRepository->save($quote->collectTotals());
        $jsonData = json_encode($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}