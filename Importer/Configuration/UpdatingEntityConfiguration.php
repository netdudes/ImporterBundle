<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

class UpdatingEntityConfiguration extends EntityConfiguration implements UpdatingEntityConfigurationInterface
{
    protected $updateMatchFields = [];

    /**
     * @return array
     */
    public function getUpdateMatchFields()
    {
        return $this->updateMatchFields;
    }

    /**
     * @param array $updateMatchFields
     */
    public function setUpdateMatchFields($updateMatchFields)
    {
        $this->updateMatchFields = $updateMatchFields;
    }

    /**
     * @param EntityConfiguration $entityConfiguration
     *
     * @return UpdatingEntityConfiguration
     */
    public static function createFromEntityConfiguration(EntityConfiguration $entityConfiguration)
    {
        $self = new self();
        $self->setClass($entityConfiguration->getClass());
        $self->setFields($entityConfiguration->getFields());

        return $self;
    }
}
