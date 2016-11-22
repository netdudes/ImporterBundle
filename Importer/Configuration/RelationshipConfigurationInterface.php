<?php
namespace Netdudes\ImporterBundle\Importer\Configuration;

use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;

interface RelationshipConfigurationInterface extends ConfigurationInterface
{
    /**
     * @return string
     */
    public function getAssignmentMethod();

    /**
     * @return string
     */
    public function getOwnerLookupFieldName();

    /**
     * @return LookupFieldConfiguration
     */
    public function getOwnerLookupConfigurationField();

    /**
     * @return string
     */
    public function getRelatedLookupFieldName();

    /**
     * @return LookupFieldConfiguration
     */
    public function getRelatedLookupConfigurationField();
}
