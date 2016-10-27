<?php

namespace Netdudes\ImporterBundle\Importer\Event\Error;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;
use Symfony\Component\EventDispatcher\Event;

class InterpreterExceptionEvent extends Event
{
    /**
     * @var LogInterface
     */
    private $log;

    /**
     * @var InterpreterException
     */
    private $exception;

    /**
     * @var bool
     */
    private $stopped = false;

    /**
     * @var int
     */
    private $index;

    /**
     * @param LogInterface         $log
     * @param InterpreterException $exception
     * @param int                  $index
     */
    public function __construct(
        LogInterface $log,
        InterpreterException $exception,
        $index
    ) {
        $this->log = $log;
        $this->exception = $exception;
        $this->index = $index;
    }

    /**
     * @return LogInterface
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return InterpreterException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * @param bool $stopped
     */
    public function setStopped($stopped)
    {
        $this->stopped = $stopped;
    }
}
