<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

interface FieldInterpreterInterface
{
    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     * 
     * @return mixed|null
     */
    public function interpret(FieldConfigurationInterface $configuration, $value);
}
