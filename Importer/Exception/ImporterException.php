<?php

namespace Netdudes\ImporterBundle\Importer\Exception;

use Exception;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;

class ImporterException extends \Exception
{
    /**
     * @var ImporterErrorInterface
     */
    private $error;

    /**
     * @param ImporterErrorInterface $error
     * @param string                 $message
     * @param int                    $code
     * @param Exception|null         $previous
     */
    public function __construct(ImporterErrorInterface $error, $message = "", $code = 0, Exception $previous = null)
    {
        $this->error = $error;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ImporterErrorInterface
     */
    public function getError()
    {
        return $this->error;
    }
}
