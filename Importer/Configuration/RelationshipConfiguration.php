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

    /**
     * @param mixed $ownerLookupConfigurationField
     */
    public function setOwnerLookupConfigurationField($ownerLookupConfigurationField)
    {
        $this->ownerLookupConfigurationField = $ownerLookupConfigurationField;
    }

    /**
     * @param mixed $ownerLookupFieldName
     */
    public function setOwnerLookupFieldName($ownerLookupFieldName)
    {
        $this->ownerLookupFieldName = $ownerLookupFieldName;
    }

    /**
     * @param mixed $relatedLookupConfigurationField
     */
    public function setRelatedLookupConfigurationField($relatedLookupConfigurationField)
    {
        $this->relatedLookupConfigurationField = $relatedLookupConfigurationField;
    }

    /**
     * @param mixed $relatedLookupFieldName
     */
    public function setRelatedLookupFieldName($relatedLookupFieldName)
    {
        $this->relatedLookupFieldName = $relatedLookupFieldName;
    }

    public function getAssignmentMethod()
    {
        return $this->assignementMethod;
    }

    public function getOwnerLookupFieldName()
    {
        return $this->ownerLookupFieldName;
    }

    public function getOwnerLookupConfigurationField()
    {
        return $this->ownerLookupConfigurationField;
    }

    public function getRelatedLookupFieldName()
    {
        return $this->relatedLookupFieldName;
    }

    public function getRelatedLookupConfigurationField()
    {
        return $this->relatedLookupConfigurationField;
    }

    public function getFieldNames()
    {
        return [
            $this->getOwnerLookupFieldName(),
            $this->getRelatedLookupFieldName()
        ];
    }
}
