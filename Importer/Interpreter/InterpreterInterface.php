<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter;

interface InterpreterInterface
{
    /**
     * @param array $data
     * @param bool  $associative
     *
     * @return array|void
     */
    public function interpret(array $data, $associative = true);
}
