<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\BooleanFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

class BooleanFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @var array
     */
    private $falseRepresentations = [
        'false',
        false,
        '0',
        0,
        'no'
    ];

    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     *
     * @return bool|null
     */
    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof BooleanFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        if (null === $value) {
            return null;
        }

        return !in_array($value, $this->falseRepresentations, true);
    }
}
