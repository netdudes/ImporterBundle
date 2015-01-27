<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ImporterInterface
{
    /**
     * @param $data
     *
     * @return object[]
     */
    public function import($data);

    /**
     * @param $filename
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
     * @param callable $callable
     */
    public function registerPostProcess(callable $callable);

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * @param          $event
     * @param callable $eventListener
     */
    public function addEventListener($event, callable $eventListener);

    /**
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber);
}
