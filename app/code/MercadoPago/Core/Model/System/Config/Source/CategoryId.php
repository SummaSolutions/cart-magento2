<?php
namespace MercadoPago\Core\Model\System\Config\Source;

/**
 * Class CategoryId
 *
 * @package MercadoPago\Core\Model\System\Config\Source
 */
class CategoryId
    implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \MercadoPago\Core\Helper\DataFactory
     */
    protected $coreHelperFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MercadoPago\Core\Helper\DataFactory               $coreHelperFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MercadoPago\Core\Helper\DataFactory $coreHelperFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->coreHelperFactory = $coreHelperFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $this->coreHelperFactory->create()->log("Get Categories... ", 'mercadopago.log');

        $response = \MercadoPago_Lib_RestClient::get("/item_categories");
        $this->coreHelperFactory->create()->log("API item_categories", 'mercadopago.log', $response);

        $response = $response['response'];

        $cat = array();
        $count = 0;
        foreach ($response as $v) {
            //force category others first
            if ($v['id'] == "others") {
                $cat[0] = array('value' => $v['id'], 'label' => __($v['description']));
            } else {
                $count++;
                $cat[$count] = array('value' => $v['id'], 'label' => __($v['description']));
            }

        };

        //force order by key
        ksort($cat);

        return $cat;
    }

}
