<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

class RelationshipConfiguration implements RelationshipConfigurationInterface
{
    protected $relatedLookupConfigurationField;

    protected $relatedLookupFieldName;

    protected $ownerLookupConfigurationField;

    protected $ownerLookupFieldName;

    protected $assignementMethod;

    /**
     * @param mixed $assignementMethod
     */
    public function setAssignementMethod($assignementMethod)
    {
        $this->assignementMethod = $assignementMethod;
    }

    public function getAssignmentMethod()
    {
        return $this->assignementMethod;
    }

    public function getOwnerLookupConfigurationField()
    {
        return $this->ownerLookupConfigurationField;
    }

    /**
     * @param mixed $ownerLookupConfigurationField
     */
    public function setOwnerLookupConfigurationField($ownerLookupConfigurationField)
    {
        $this->ownerLookupConfigurationField = $ownerLookupConfigurationField;
    }

    public function getRelatedLookupConfigurationField()
    {
        return $this->relatedLookupConfigurationField;
    }

    /**
     * @param mixed $relatedLookupConfigurationField
     */
    public function setRelatedLookupConfigurationField($relatedLookupConfigurationField)
    {
        $this->relatedLookupConfigurationField = $relatedLookupConfigurationField;
    }

    public function getFieldNames()
    {
        return [
            $this->getOwnerLookupFieldName(),
            $this->getRelatedLookupFieldName()
        ];
    }

    public function getOwnerLookupFieldName()
    {
        return $this->ownerLookupFieldName;
    }

    /**
     * @param mixed $ownerLookupFieldName
     */
    public function setOwnerLookupFieldName($ownerLookupFieldName)
    {
        $this->ownerLookupFieldName = $ownerLookupFieldName;
    }

    public function getRelatedLookupFieldName()
    {
        return $this->relatedLookupFieldName;
    }

    /**
     * @param mixed $relatedLookupFieldName
     */
    public function setRelatedLookupFieldName($relatedLookupFieldName)
    {
        $this->relatedLookupFieldName = $relatedLookupFieldName;
    }
}
