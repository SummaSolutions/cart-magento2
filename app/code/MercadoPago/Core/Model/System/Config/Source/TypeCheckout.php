<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class TypeCheckout
    implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $arr = [
            ["value" => "iframe", 'label' => __("Iframe")],
            ["value" => "redirect", 'label' => __("Redirect")],
            ["value" => "lightbox", 'label' => __("Lightbox")]
        ];

        return $arr;
    }
}
