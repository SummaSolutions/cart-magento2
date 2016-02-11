<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class CategoryId implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        //Mage::helper('mercadopago')->log("Get Categories... ", 'mercadopago.log');

        $response = \MercadoPago_Lib_RestClient::get("/item_categories");
        //Mage::helper('mercadopago')->log("API item_categories", 'mercadopago.log', $response);

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
