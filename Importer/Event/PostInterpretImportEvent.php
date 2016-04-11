<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;

class PostInterpretImportEvent extends PostBindDataImportEvent
{
    /**
     * @deprecated Class Netdudes\ImporterBundle\Importer\Event\PostInterpretImportEvent is deprecated since 1.0-dev and will be removed in 1.0. Use Netdudes\ImporterBundle\Importer\Event\PostBindDataImportEvent instead.
     *
     * @param object               $entity
     * @param InterpreterInterface $interpreter
     */
    public function __construct($entity, InterpreterInterface $interpreter)
    {
        trigger_error('Class Netdudes\ImporterBundle\Importer\Event\PostInterpretImportEvent is deprecated since 1.0-dev and will be removed in 1.0. Use Netdudes\ImporterBundle\Importer\Event\PostBindDataImportEvent instead.', E_USER_DEPRECATED);

        parent::__construct($entity, $interpreter);
    }
}
