<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\UpdatingEntityConfiguration;

class UpdatingEntityDataInterpreter extends EntityDataInterpreter
{
    /**
     * @var UpdatingEntityConfiguration
     */
    protected $configuration;

    private $propertyCache = [];

    protected function getEntity(array $interpretedData)
    {
        if (!is_null($entity = $this->findEntity($interpretedData))) {
            return $entity;
        }

        return parent::getEntity($interpretedData);
    }

    private function findEntity(array $interpretedData)
    {
        $queryParameters = [];
        foreach ($this->configuration->getUpdateMatchFields() as $field) {
            $entityPropertyName = $this->getEntityPropertyByFieldName($field);
            $queryParameters[$entityPropertyName] = $interpretedData[$entityPropertyName];
        }

        $repository = $this
            ->entityManager
            ->getRepository($this->configuration->getClass());

        return $repository
            ->findOneBy($queryParameters);
    }

    private function getEntityPropertyByFieldName($fieldName)
    {
        if (!count($this->propertyCache)) {
            /** @var $configurationField FieldConfigurationInterface */
            foreach ($this->configuration->getFields() as $fieldName => $configurationField) {
                $this->propertyCache[$fieldName] = $configurationField->getField();
            }
        }

        return $this->propertyCache[$fieldName];
    }

}