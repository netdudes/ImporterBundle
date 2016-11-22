<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

class LiteralFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     * 
     * @return mixed|null
     */
    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (trim($value) === '') {
            return null;
        }

        return $value;
    }
}
