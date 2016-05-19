<?php
namespace MercadoPago\MercadoEnvios\Model\System\Config\Source;

/**
 * Class Category
 *
 * @package MercadoPago\Core\Model\System\Config\Source
 */
class Method
    implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \MercadoPago\Core\Helper\Data
     */
    protected $coreHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_countryOptions = [
        'mla' => [
            ['value' => 73328, 'label' => 'Normal'],
            ['value' => 73330, 'label' => 'Prioritario']
        ],
        'mlb' => [
            ['value' => 100009, 'label' => 'Normal'],
            ['value' => 182, 'label' => 'Expresso'],
        ],
        'mlm' => [
            ['value' => 501245, 'label' => 'DHL Estándar'],
            ['value' => 501345, 'label' => 'DHL Express'],
        ]
    ];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MercadoPago\Core\Helper\Data                      $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\Data $coreHelper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Return key sorted shop item categories
     * @return array
     */
    public function toOptionArray()
    {
        $country = $this->scopeConfig->getValue('payment/mercadopago/country');
        if ($this->_countryOptions[$country]) {
            return $this->_countryOptions[$country];
        }
        return null;
    }

    public function getAvailableCodes() {
        $methods = $this->toOptionArray();
        $codes = [];
        foreach ($methods as $method) {
            $codes[] = $method['value'];
        }
        return $codes;
    }

}
