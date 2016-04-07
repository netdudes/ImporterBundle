<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;

class RelationshipConfiguration implements RelationshipConfigurationInterface
{
    /**
     * @var LookupFieldConfiguration
     */
    protected $relatedLookupConfigurationField;

    /**
     * @var string
     */
    protected $relatedLookupFieldName;

    /**
     * @var LookupFieldConfiguration
     */
    protected $ownerLookupConfigurationField;

    /**
     * @var string
     */
    protected $ownerLookupFieldName;

    /**
     * @var string
     */
    protected $assignmentMethod;

    /**
     * @param string $assignmentMethod
     */
    public function setAssignmentMethod($assignmentMethod)
    {
        $this->assignmentMethod = $assignmentMethod;
    }

    /**
     * @return string
     */
    public function getAssignmentMethod()
    {
        return $this->assignmentMethod;
    }

    /**
     * @return LookupFieldConfiguration
     */
    public function getOwnerLookupConfigurationField()
    {
        return $this->ownerLookupConfigurationField;
    }

    /**
     * @param LookupFieldConfiguration $ownerLookupConfigurationField
     */
    public function setOwnerLookupConfigurationField(LookupFieldConfiguration $ownerLookupConfigurationField)
    {
        $this->ownerLookupConfigurationField = $ownerLookupConfigurationField;
    }

    /**
     * @return LookupFieldConfiguration
     */
    public function getRelatedLookupConfigurationField()
    {
        return $this->relatedLookupConfigurationField;
    }

    /**
     * @param LookupFieldConfiguration $relatedLookupConfigurationField
     */
    public function setRelatedLookupConfigurationField(LookupFieldConfiguration $relatedLookupConfigurationField)
    {
        $this->relatedLookupConfigurationField = $relatedLookupConfigurationField;
    }

    /**
     * @return string[]
     */
    public function getFieldNames()
    {
        return [
            $this->getOwnerLookupFieldName(),
            $this->getRelatedLookupFieldName()
        ];
    }

    /**
     * @return string
     */
    public function getOwnerLookupFieldName()
    {
        return $this->ownerLookupFieldName;
    }

    /**
     * @param string $ownerLookupFieldName
     */
    public function setOwnerLookupFieldName($ownerLookupFieldName)
    {
        $this->ownerLookupFieldName = $ownerLookupFieldName;
    }

    /**
     * @return string
     */
    public function getRelatedLookupFieldName()
    {
        return $this->relatedLookupFieldName;
    }

    /**
     * @param string $relatedLookupFieldName
     */
    public function setRelatedLookupFieldName($relatedLookupFieldName)
    {
        $this->relatedLookupFieldName = $relatedLookupFieldName;
    }
}
