<?php
namespace MercadoPago\Core\Model\System\Config\Source;

class Installments implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $installment = array();

        $installment[] = array("value" => 1, "label" => "1");
        $installment[] = array("value" => 2, "label" => "2");
        $installment[] = array("value" => 3, "label" => "3");
        $installment[] = array("value" => 4, "label" => "4");
        $installment[] = array("value" => 5, "label" => "5");
        $installment[] = array("value" => 6, "label" => "6");
        $installment[] = array("value" => 9, "label" => "9");
        $installment[] = array("value" => 10, "label" => "10");
        $installment[] = array("value" => 12, "label" => "12");
        $installment[] = array("value" => 15, "label" => "15");
        $installment[] = array("value" => 24, "label" => "24");

        return $installment;
    }

}
