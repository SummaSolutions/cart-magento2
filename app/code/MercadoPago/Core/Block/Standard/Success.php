<?php
namespace MercadoPago\Core\Block\Standard;


class Success
    extends \MercadoPago\Core\Block\AbstractSuccess
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('standard/success.phtml');
    }

}