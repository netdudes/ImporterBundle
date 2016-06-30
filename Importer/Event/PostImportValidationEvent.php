<?php

namespace Netdudes\ImporterBundle\Importer\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class PostImportValidationEvent extends Event
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $validationViolationList;

    /**
     * @var object
     */
    private $entity;

    /**
     * @param ConstraintViolationListInterface $constraintViolationList
     * @param object                           $entity
     */
    public function __construct(ConstraintViolationListInterface $constraintViolationList, $entity)
    {
        $this->validationViolationList = $constraintViolationList;
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getValidationViolations()
    {
        return $this->validationViolationList;
    }

    /**
     * @param ConstraintViolationInterface $validationViolation
     */
    public function addValidationViolation(ConstraintViolationInterface $validationViolation)
    {
        $this->validationViolation->add($validationViolation);
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}