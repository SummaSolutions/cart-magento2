<?php
namespace MercadoPago\Core\Helper\Message;


interface MessageInterface
{
    public function getMessageMap();


    /**
     * @param      $key
     * @param null $args array()
     *
     * @return string
     */
    public function getMessage($key);

}