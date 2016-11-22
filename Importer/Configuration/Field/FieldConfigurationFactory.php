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
        $fieldConfiguration = $this->getFieldConfiguration($fieldConfigurationNode);

        $property = $this->getProperty($fieldConfigurationNode);

        $fieldConfiguration->setField($property);
        $fieldConfiguration->setHelp($this->getNodeData($fieldConfigurationNode, 'help'));

        return $fieldConfiguration;
    }

    /**
     * @param array $fieldConfigurationNode
     *
     * @throws \Exception
     *
     * @return FieldConfigurationInterface
     */
    private function getFieldConfiguration(array $fieldConfigurationNode)
    {
        $type = $this->getNodeData($fieldConfigurationNode, 'type');
        switch ($type) {
            case ('datetime'):
                $fieldConfiguration = new DateTimeFieldConfiguration();
                $this->setFormatIfDefined($fieldConfigurationNode, $fieldConfiguration);

                return $fieldConfiguration;
            case ('date'):
                $fieldConfiguration = new DateFieldConfiguration();
                $this->setFormatIfDefined($fieldConfigurationNode, $fieldConfiguration);

                return $fieldConfiguration;
            case ('file'):
                return new FileFieldConfiguration();
            case ('boolean'):
                return new BooleanFieldConfiguration();
            case (null):
                return new LiteralFieldConfiguration();
            case class_exists($type):
                return $this->createFieldConfigurationOfClass($type);
            default:
                throw new \Exception("$type is not supported.");
        }
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
     * @param array  $node
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

    /**
     * @param array $fieldConfigurationNode
     *
     * @throws MissingParameterException
     *
     * @return mixed
     */
    private function getProperty(array $fieldConfigurationNode)
    {
        $property = $this->getNodeData($fieldConfigurationNode, 'property');
        if (null === $property) {
            $exception = new MissingParameterException('Missing property parameter in field configuration');
            $exception->setParameter('property');
            throw $exception;
        }

        return $property;
    }

    /**
     * @param array                      $fieldConfigurationNode
     * @param DateTimeFieldConfiguration $fieldConfiguration
     */
    private function setFormatIfDefined(array $fieldConfigurationNode, DateTimeFieldConfiguration $fieldConfiguration)
    {
        $format = $this->getNodeData($fieldConfigurationNode, 'format');
        is_null($format) ?: $fieldConfiguration->setFormat($format);
    }
}
