<?php
namespace MercadoPago\MercadoEnvios\Model\Adminhtml\Attribute\Validation;


/**
 * Class Mapping
 *
 * @package MercadoPago\MercadoEnvios\Model\Adminhtml\Attribute\Validation
 */
class Mapping
    extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{

    /**
     * Validates attribute mapping entries
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $mappingValues = (array)$this->getValue(); //get the value from our config
        $attributeCodes = [];
        if ($this->_config->getValue('carriers/mercadoenvios/active')) {
            foreach ($mappingValues as $value) {
                if (in_array($value['attribute_code'], $attributeCodes)) {
                    throw new \Exception(__('Cannot repeat Magento Product size attributes'));
                }

                $attributeCodes[] = $value['attribute_code'];
            }
        }

        return parent::save();
    }

}