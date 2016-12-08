<?php
namespace Netdudes\ImporterBundle\Importer\EventListener;

use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEvent;
use Netdudes\ImporterBundle\Importer\Event\ImportEvents;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidValueException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidRowException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        if ($exception instanceof InvalidValueException) {
            $message = "The value “{$exception->getActualValue()}” is invalid.";

            if (null !== $exception->getAllowedValues()) {
                $allowedValues = implode(', ', $exception->getAllowedValues());
                $message .= " The following values are valid: “{$allowedValues}”.";
            }

            if (null !== $exception->getExpectedFormat()) {
                $message .= " It does not match to format “{$exception->getExpectedFormat()}”.";
            }

            return $message;
        } elseif ($exception instanceof LookupFieldException) {
            return $this->buildLookupFieldExceptionMessage($exception);
        } elseif ($exception instanceof RowSizeMismatchException) {
            return "Row is expected to contain {$exception->getExpectedSize()}, found to have {$exception->getFoundSize()} fields instead.";
        } elseif ($exception instanceof InvalidRowException) {
            return $this->buildInvalidRowExceptionMessage($exception);
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
        $message = "Could not find $class with {$lookupField} “{$exception->getValue()}”.";

        return $message;
    }

    /**
     * @param InvalidRowException $exception
     *
     * @return string
     */
    private function buildInvalidRowExceptionMessage(InvalidRowException $exception)
    {
        $message = '';
        foreach ($exception->getErrors() as $error) {
            $fieldName = $error->getFieldName();
            if ($fieldName !== '') {
                $fieldName = '<kbd>' . $fieldName . '</kbd>: ';
            }

            $message .= '<li>' . $fieldName . $this->resolveInterpreterExceptionMessage($error->getException()) . '</li>';
        }

        return '<ul>' . $message . '</ul>';
    }
}
