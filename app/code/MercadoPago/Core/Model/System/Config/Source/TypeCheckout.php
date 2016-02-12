<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class TypeCheckout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $arr = array(
            array("value"=> "iframe", 'label'=>__("Iframe")),
            array("value"=> "redirect", 'label'=>__("Redirect")),
            array("value"=> "lightbox", 'label'=>__("Lightbox"))
        );

        return $arr;
    }
}
