<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;

interface ImporterInterface
{
    /**
     * @param string $data
     * @param bool   $dryRun
     */
    public function import($data, $dryRun = false);

    /**
     * @param InterpreterErrorHandlerInterface $lineErrorHandler
     */
    public function registerInterpreterErrorHandler(InterpreterErrorHandlerInterface $lineErrorHandler);

    /**
     * @param ImporterErrorHandlerInterface $fileErrorHandler
     */
    public function registerImporterErrorHandler(ImporterErrorHandlerInterface $fileErrorHandler);

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * @return LogInterface
     */
    public function getLog();
}
