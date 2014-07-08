<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

abstract class AbstractFieldConfiguration implements FieldConfigurationInterface
{
    protected $field;

    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}