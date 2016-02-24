<?php
namespace Mercadopago\Core\Controller\Notifications;


/**
 * Class Standard
 *
 * @package Mercadopago\Core\Controller\Notifications
 */
class Standard
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

    const LOG_NAME = 'standard_notification';


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
        //notification received
        $this->coreHelper->log("Standard Received notification", self::LOG_NAME, $request->getParams());

        $id = $request->getParam('id');
        $topic = $request->getParam('topic');

        if (!empty($id) && $topic == 'merchant_order') {
            $response = $this->coreModel->getMerchantOrder($id);
            $this->coreHelper->log("Return merchant_order", self::LOG_NAME, $response);
            if ($response['status'] == 200 || $response['status'] == 201) {
                $merchant_order = $response['response'];

                if (count($merchant_order['payments']) > 0) {
                    $data = $this->_getDataPayments($merchant_order);
                    $status_final = $this->_getStatusFinal($data['status']);
                    $this->coreHelper->log("Update Order", self::LOG_NAME);
                    $this->coreModel->updateOrder($data);

                    if ($status_final != false) {
                        $data['status_final'] = $status_final;
                        $this->coreHelper->log("Received Payment data", self::LOG_NAME, $data);
                        $setStatusResponse = $this->coreModel->setStatusOrder($data);
                        $this->getResponse()->setBody($setStatusResponse['text']);
                        $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
                    }

                    return;
                }
            }
        }

        $this->coreHelper->log("Merchant Order not found", self::LOG_NAME, $request->getParams());
        $this->getResponse()->setBody("Merchant Order not found");
        $this->getResponse()->setHttpResponseCode(\MercadoPago\Core\Helper\Response::HTTP_NOT_FOUND);
    }

    /**
     * Check if status is final in case of multiple card payment
     * @param $dataStatus
     *
     * @return bool|mixed|string
     */
    protected function _getStatusFinal($dataStatus)
    {
        $status_final = "";
        $statuses = explode('|', $dataStatus);
        foreach ($statuses as $status) {
            $status = str_replace(' ', '', $status);
            if ($status_final == "") {
                $status_final = $status;
            } else {
                if ($status_final != $status) {
                    $status_final = false;
                }
            }
        }

        return $status_final;
    }

    /**
     * Collect data from notification content
     * @param $merchantOrder
     *
     * @return array
     */
    protected function _getDataPayments($merchantOrder)
    {
        $data = array();
        foreach ($merchantOrder['payments'] as $payment) {
            $response = $this->coreModel->getPayment($payment['id']);
            $payment = $response['response']['collection'];
            $data = $this->_formatArrayPayment($data, $payment);
        }

        return $data;
    }


    /**
     * Collect data from notification content to update order info
     * @param $data
     * @param $payment
     *
     * @return mixed
     */
    protected function _formatArrayPayment($data, $payment)
    {
        $this->coreHelper->log("Format Array", self::LOG_NAME);

        $fields = array(
            "status",
            "status_detail",
            "id",
            "payment_method_id",
            "transaction_amount",
            "total_paid_amount",
            "coupon_amount",
            "installments",
            "shipping_cost",
        );

        foreach ($fields as $field) {
            if (isset($payment[$field])) {
                if (isset($data[$field])) {
                    $data[$field] .= " | " . $payment[$field];
                } else {
                    $data[$field] = $payment[$field];
                }
            }
        }

        if (isset($payment["last_four_digits"])) {
            if (isset($data["trunc_card"])) {
                $data["trunc_card"] .= " | " . "xxxx xxxx xxxx " . $payment["last_four_digits"];
            } else {
                $data["trunc_card"] = "xxxx xxxx xxxx " . $payment["last_four_digits"];
            }
        }

        if (isset($payment['cardholder']['name'])) {
            if (isset($data["cardholder_name"])) {
                $data["cardholder_name"] .= " | " . $payment["cardholder"]["name"];
            } else {
                $data["cardholder_name"] = $payment["cardholder"]["name"];
            }
        }

        if (isset($payment['statement_descriptor'])) {
            $data['statement_descriptor'] = $payment['statement_descriptor'];
        }

        $data['external_reference'] = $payment['external_reference'];
        $data['payer_first_name'] = $payment['payer']['first_name'];
        $data['payer_last_name'] = $payment['payer']['last_name'];
        $data['payer_email'] = $payment['payer']['email'];

        return $data;
    }

}