<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\LiteralFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LiteralFieldInterpreter;

class LiteralFieldInterpreterTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpret()
    {
        $config = new LiteralFieldConfiguration();
        $interpreter = new LiteralFieldInterpreter();

        $this->assertEquals('SOME_TEST_STRING"}{"][PA98(&876\t[333*/-*', $interpreter->interpret($config, 'SOME_TEST_STRING"}{"][PA98(&876\t[333*/-*'));
    }

}
