<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

interface RelationshipConfigurationInterface extends ConfigurationInterface
{
    public function getAssignmentMethod();

    public function getOwnerLookupFieldName();

    public function getOwnerLookupConfigurationField();

    public function getRelatedLookupFieldName();

    public function getRelatedLookupConfigurationField();
}
