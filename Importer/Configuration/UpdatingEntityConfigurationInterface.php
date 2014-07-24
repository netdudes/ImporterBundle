<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

interface UpdatingEntityConfigurationInterface extends EntityConfigurationInterface
{
    /**
     * @param array $updateMatchFields
     */
    public function setUpdateMatchFields($updateMatchFields);

    /**
     * @return array
     */
    public function getUpdateMatchFields();
}