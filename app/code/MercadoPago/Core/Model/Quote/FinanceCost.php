<?php
namespace MercadoPago\Core\Model\Quote;

class FinanceCost extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\Event\ManagerInterface         $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \Magento\SalesRule\Model\Validator                $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\RequestInterface           $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->setCode('finance_cost');
        $this->request = $request;
    }

    /**
     * Determine if should apply subtotal
     *
     * @param $address
     *
     * @return bool
     */
    protected function _getFinancingCondition($address)
    {
        $req = $this->request->getParam('total_amount');

        return (!empty($req) && $address->getAddressType() == \Magento\Customer\Helper\Address::TYPE_SHIPPING);

    }
    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($this->_getFinancingCondition($address)) {
            $postData = $this->request->getPost();
            parent::collect($quote, $shippingAssignment, $total);

            $totalAmount = (float)$postData['total_amount'];
            $amount = (float)$postData['amount'] - (float)$postData['mercadopago-discount-amount'];
            $balance = $totalAmount - $amount;

            $address->setFinanceCostAmount($balance);
            $address->setBaseFinanceCostAmount($balance);

            $this->_setAmount($balance);
            $this->_setBaseAmount($balance);

            return $this;
        }

        if ($address->getAddressType() == \Magento\Customer\Helper\Address::TYPE_SHIPPING) {
            $address->setFinanceCostAmount(0);
            $address->setBaseFinanceCostAmount(0);
        }

        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getFinanceCostAmount();

        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Financing Cost'),
                'value' => $amount
            ];
        }
        return $result;
    }
}
