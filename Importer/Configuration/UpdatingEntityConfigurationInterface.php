<?php
namespace Netdudes\ImporterBundle\Importer\Configuration;

interface UpdatingEntityConfigurationInterface extends EntityConfigurationInterface
{
    /**
     * @param array $updateMatchFields
     */
    public function setUpdateMatchFields(array $updateMatchFields);

    /**
     * @return array
     */
    public function getUpdateMatchFields();
}
