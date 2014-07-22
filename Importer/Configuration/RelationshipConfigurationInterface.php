<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;

interface RelationshipConfigurationInterface extends ConfigurationInterface
{
    public function getAssignmentMethod();

    public function getOwnerLookupFieldName();

    /**
     * @return LookupFieldConfiguration
     */
    public function getOwnerLookupConfigurationField();

    public function getRelatedLookupFieldName();

    /**
     * @return LookupFieldConfiguration
     */
    public function getRelatedLookupConfigurationField();
}
