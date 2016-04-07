<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;

interface ImporterInterface
{
    /**
     * @param array $data
     *
     * @return object[]
     */
    public function import($data);

    /**
     * @param string $filename
     *
     * @return object[]
     */
    public function importFile($filename);

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
}
