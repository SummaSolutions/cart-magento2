<?php
namespace MercadoPago\Core\Block\Custom;

/**
 * Class Success
 *
 * @package MercadoPago\Core\Block\Standard
 */

class Success
    extends \MercadoPago\Core\Block\AbstractSuccess
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('custom/success.phtml');
    }

}