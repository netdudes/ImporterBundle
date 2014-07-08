<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter;

class EntityDataInterpreterTest
{
    public function testInterpretAssociativeData()
    {

        $data = [
            'a' => 1,
            'b' => 2,
            'c' => 3
        ];
    }
}

class TestEntity
{
    private $key;

    function __construct($key)
    {
        $this->key = $key;
    }

    function __toString()
    {
        return $this->key;
    }
}