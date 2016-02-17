<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class CategoryId
    implements \Magento\Framework\Option\ArrayInterface
{
    protected $coreHelper;
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MercadoPago\Core\Helper\Data                      $coreHelperFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\Data $coreHelper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->coreHelper = $coreHelper;
    }

    public function toOptionArray()
    {
        $this->coreHelper->log("Get Categories... ", 'mercadopago');

        $response = \MercadoPago_Lib_RestClient::get("/item_categories");
        $this->coreHelper->log("API item_categories", 'mercadopago', $response);

        $response = $response['response'];

        $cat = array();
        $count = 0;
        foreach ($response as $v) {
            //force category others first
            if ($v['id'] == "others") {
                $cat[0] = ['value' => $v['id'], 'label' => __($v['description'])];
            } else {
                $count++;
                $cat[$count] = ['value' => $v['id'], 'label' => __($v['description'])];
            }

        };

        //force order by key
        ksort($cat);

        return $cat;
    }

}
