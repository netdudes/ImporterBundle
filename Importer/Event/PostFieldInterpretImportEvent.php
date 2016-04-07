<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Symfony\Component\EventDispatcher\Event;

class PostFieldInterpretImportEvent extends Event
{
    /**
     * @var FieldConfigurationInterface
     */
    public $fieldConfiguration;

    /**
     * @var mixed
     */
    public $interpretedValue;

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param mixed                       $interpretedValue
     */
    public function __construct(FieldConfigurationInterface $fieldConfiguration, $interpretedValue)
    {
        $this->fieldConfiguration = $fieldConfiguration;
        $this->interpretedValue = $interpretedValue;
    }
}
