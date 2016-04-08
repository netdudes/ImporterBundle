<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractImportEvent extends Event
{
    /**
     * @var InterpreterInterface
     */
    public $interpreter;

    /**
     * @param InterpreterInterface $interpreter
     */
    public function __construct(InterpreterInterface $interpreter)
    {
        $this->interpreter = $interpreter;
    }
}
