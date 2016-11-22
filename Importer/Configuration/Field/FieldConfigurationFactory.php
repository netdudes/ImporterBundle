<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Exception\InvalidFieldConfigurationClassException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\MissingParameterException;

class FieldConfigurationFactory
{
    /**
     * @param array $fieldConfigurationNode
     *
     * @throws InvalidFieldConfigurationClassException
     * @throws MissingParameterException
     *
     * @return FieldConfigurationInterface
     */
    public function create(array $fieldConfigurationNode)
    {
        $type = $this->getNodeData($fieldConfigurationNode, 'type');
        if (null !== $type) {
            $fieldConfiguration = $this->createFieldConfigurationOfClass($type);
        } else {
            $fieldConfiguration = $this->createLiteralFieldConfiguration();
        }

        $property = $this->getNodeData($fieldConfigurationNode, 'property');
        if (null === $property) {
            $exception = new MissingParameterException('Missing property parameter in field configuration');
            $exception->setParameter('property');
            throw $exception;
        }

        $fieldConfiguration->setField($property);

        return $fieldConfiguration;
    }

    /**
     * @param string $class
     *
     * @throws InvalidFieldConfigurationClassException
     *
     * @return FieldConfigurationInterface
     */
    private function createFieldConfigurationOfClass($class)
    {
        if (!class_exists($class)) {
            throw new InvalidFieldConfigurationClassException("The $class class does not exist");
        }
        if (!is_subclass_of($class, FieldConfigurationInterface::class)) {
            throw new InvalidFieldConfigurationClassException("The $class class must implement field configuration interface");
        }

        $fieldConfiguration = new $class();

        return $fieldConfiguration;
    }

    /**
     * @return LiteralFieldConfiguration
     */
    private function createLiteralFieldConfiguration()
    {
        $fieldConfiguration = new LiteralFieldConfiguration();

        return $fieldConfiguration;
    }

    /**
     * @param array $node
     * @param string $key
     *
     * @return mixed|null
     */
    private function getNodeData(array $node, $key)
    {
        if (array_key_exists($key, $node)) {
            return $node[$key];
        }

        return null;
    }
}
