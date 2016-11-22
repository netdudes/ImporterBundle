<?php
namespace Netdudes\ImporterBundle\Importer\Event\Error;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;
use Symfony\Component\EventDispatcher\Event;

class InterpreterExceptionEventFactory extends Event
{
    /**
     * @var LogInterface
     */
    private $log;

    /**
     * @param LogInterface $log
     */
    public function __construct(LogInterface $log)
    {
        $this->log = $log;
    }

    /**
     * @param InterpreterException $exception
     * @param int                  $index
     *
     * @return InterpreterExceptionEvent
     */
    public function create(InterpreterException $exception, $index)
    {
        $interpreterExceptionEvent = new InterpreterExceptionEvent($this->log, $exception, $index);

        return $interpreterExceptionEvent;
    }
}
