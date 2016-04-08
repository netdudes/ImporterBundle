<?php

namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;

class PreBindDataImportEvent extends AbstractImportEvent
{
    /**
     * @var object
     */
    private $entity;

    /**
     * @param object               $entity
     * @param InterpreterInterface $interpreter
     */
    public function __construct($entity, InterpreterInterface $interpreter)
    {
        parent::__construct($interpreter);
        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}