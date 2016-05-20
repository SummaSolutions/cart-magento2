<?php

namespace MercadoPago\MercadoEnvios\Helper;

class ItemData
    extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function itemHasChildren($item)
    {
        $children = $item->getChildren();

        return (!empty($children) || in_array('getHasChildren',get_class_methods($item)) && $item->getHasChildren());
    }

    public function itemGetQty($item)
    {
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }
        $qty = (in_array('getQty',get_class_methods($item))) ? $item->getQty() : $item->getQtyOrdered();

        return $qty;
    }
}