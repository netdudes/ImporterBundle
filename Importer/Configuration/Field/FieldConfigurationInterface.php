<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

interface FieldConfigurationInterface
{
    /**
     * @return string
     */
    public function getField();

    /**
     * @param string $field
     */
    public function setField($field);
}
