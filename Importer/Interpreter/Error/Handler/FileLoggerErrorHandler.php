<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\DateTimeFormatException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidEntityException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\SetterDoesNotAllowNullException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownColumnException;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * This is a basic error handler that will log all errors to a file (or stdout by default).
 */
class FileLoggerErrorHandler implements InterpreterErrorHandlerInterface
{
    protected $file;

    private $csv;

    /**
     * @var
     */
    private $output;

    /**
     * @var int
     */
    private $lineNumberOffset;

    function __construct($output = null, $lineNumberOffset = 2)
    {
        $this->output = is_null($output) ? fopen("php://stdout", "a") : $output;
        $this->lineNumberOffset = $lineNumberOffset;
    }

    public function handle($exception, $index, $rowData)
    {
        $line = is_null($this->csv) ? implode(', ', $rowData) : $this->csv[$index];
        $lineNo = $index + $this->lineNumberOffset;
        $this->log("On file {$this->file} line {$lineNo}: {$line}");
        switch (true) {
            case $exception instanceof DateTimeFormatException:
                $this->log("Error matching date \"{$exception->getValue()}\" to format \"{$exception->getFormat()}\"", 1);
                break;
            case $exception instanceof LookupFieldException:
                $class = $exception->getFieldConfiguration()->getClass();
                $explodedClass = explode('\\', $class);
                $class = array_pop($explodedClass);
                $lookupField = $exception->getFieldConfiguration()->getLookupField();
                $this->log("Could not find $class with {$lookupField} \"{$exception->getValue()}\"", 1);
                break;
            case $exception instanceof RowSizeMismatchException:
                $this->log("Row is expected to contain {$exception->getExpectedSize()}, found to have {$exception->getFoundSize()} fields instead", 1);
                break;
            case $exception instanceof UnknownColumnException:
                $this->log("Unknown column {$exception->getColumn()} found in the uploaded data", 1);
                break;
            case $exception instanceof InvalidEntityException:
                $this->log("The uploaded entity is not valid", 1);
                $violationMessages = $this->buildInvalidEntityViolationsMessages($exception);
                foreach ($violationMessages as $message) {
                    $this->log($message, 2);
                }
                break;
            case $exception instanceof SetterDoesNotAllowNullException:
                $field = $exception->getProperty();
                $this->log("Property \"$field\" cannot be empty.", 1);
                break;
            default:
                $this->log("An internal error occurred when uploading the data", 1);
                break;
        }

    }

    protected function log($message, $indentation = 0)
    {
        $indentation = str_repeat("\t", $indentation);
        fwrite($this->output, $indentation . $message . PHP_EOL);
    }

    public function setCurrentFile($file)
    {
        $this->file = $file;

    }

    protected function buildInvalidEntityViolationsMessages($exception)
    {
        $violationsArray = [];
        $violations = $exception->getViolations();
        foreach ($violations as $violation)
        {
            $violationsArray[] = $violation;
        }

        return array_reduce(
            $violationsArray,
            function ($messages, ConstraintViolation $violation) {
                $message = "";
                if (!empty($violation->getPropertyPath())) {
                    $message .= $violation->getPropertyPath() . ": ";
                }
                $messages[] = $message . $violation->getMessage();
                return $messages;
            },
            []
        );
    }

    /**
     * @param mixed $csv
     */
    public function setCsv($csv)
    {
        $this->csv = array_slice(explode("\n", $csv), 1);
    }
}