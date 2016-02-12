<?php
namespace MercadoPago\Core\Helper\Message;


abstract class AbstractMessage implements MessageInterface
{
    public abstract function getMessageMap();


    /**
     * @param      $key
     * @param null $args array()
     *
     * @return string
     */
    public function getMessage($key)
    {
        $messageMap = $this->getMessageMap();
        if (isset($messageMap[$key])) {
            return $messageMap[$key];
        }

        return '';
    }

}