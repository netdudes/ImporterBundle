<?php
namespace Netdudes\ImporterBundle\Importer\EventListener;

use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEvent;
use Netdudes\ImporterBundle\Importer\Event\ImportEvents;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\DateTimeFormatException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidEntityException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownColumnException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;

class InterpreterExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ImportEvents::INTERPRETER_EXCEPTION => ['handleException'],
        ];
    }

    /**
     * @param InterpreterExceptionEvent $event
     */
    public function handleException(InterpreterExceptionEvent $event)
    {
        $log = $event->getLog();
        $exception = $event->getException();
        $index = $event->getIndex();

        $message = $this->resolveInterpreterExceptionMessage($exception);
        $log->addDataError($index, $message);
    }

    /**
     * @param InterpreterException $exception
     *
     * @return string
     */
    private function resolveInterpreterExceptionMessage(InterpreterException $exception)
    {
        if ($exception instanceof DateTimeFormatException) {
            return "Error matching date \"{$exception->getValue()}\" to format \"{$exception->getFormat()}\".";
        } elseif ($exception instanceof LookupFieldException) {
            return $this->buildLookupFieldExceptionMessage($exception);
        } elseif ($exception instanceof RowSizeMismatchException) {
            return "Row is expected to contain {$exception->getExpectedSize()}, found to have {$exception->getFoundSize()} fields instead.";
        } elseif ($exception instanceof UnknownColumnException) {
            return "Unknown column <kbd>{$exception->getColumn()}</kbd> found in the imported data.";
        } elseif ($exception instanceof InvalidEntityException) {
            return $this->buildInvalidEntityExceptionMessage($exception);
        } else {
            return $exception->getMessage();
        }
    }

    /**
     * @param LookupFieldException $exception
     *
     * @return string
     */
    private function buildLookupFieldExceptionMessage(LookupFieldException $exception)
    {
        $class = $exception->getFieldConfiguration()->getClass();
        $explodedClass = explode('\\', $class);
        $class = array_pop($explodedClass);
        $lookupField = $exception->getFieldConfiguration()->getLookupField();
        $message = "Could not find $class with {$lookupField} \"{$exception->getValue()}\".";

        return $message;
    }

    /**
     * @param InvalidEntityException $exception
     *
     * @return string
     */
    private function buildInvalidEntityExceptionMessage(InvalidEntityException $exception)
    {
        $violationMessages = $this->formatMessageHtmlCode($exception);

        return 'The imported entity is not valid:<ul>' . $violationMessages . '</ul>';
    }

    /**
     * @param $exception
     *
     * @return mixed
     */
    private function formatMessageHtmlCode($exception)
    {
        $violationsArray = [];
        $violations = $exception->getViolations();
        foreach ($violations as $violation) {
            $violationsArray[] = $violation;
        }

        return array_reduce(
            $violationsArray,
            function ($message, ConstraintViolation $violation) {
                return $message . '<li><kbd>' . $violation->getPropertyPath() . '</kbd> ' . $violation->getMessage() . '</li>';
            },
            ''
        );
    }
}
