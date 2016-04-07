<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;

class PostInterpretImportEvent extends AbstractImportEvent
{
    /**
     * @var object
     */
    public $entity;

    /**
     * @param object               $entity
     * @param InterpreterInterface $interpreter
     */
    public function __construct($entity, InterpreterInterface $interpreter)
    {
        parent::__construct($interpreter);
        $this->entity = $entity;
    }
}
