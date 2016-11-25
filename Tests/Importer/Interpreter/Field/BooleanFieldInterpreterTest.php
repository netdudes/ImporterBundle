<?php
namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\BooleanFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\BooleanFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;

class BooleanFieldInterpreterTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpretReturnsFalseForFalseRepresentations()
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();

        $interpreter = new BooleanFieldInterpreter();

        $this->assertFalse($interpreter->interpret($configuration, false));
        $this->assertFalse($interpreter->interpret($configuration, 'false'));
        $this->assertFalse($interpreter->interpret($configuration, 'no'));
        $this->assertFalse($interpreter->interpret($configuration, '0'));
        $this->assertFalse($interpreter->interpret($configuration, 0));
    }

    public function testInterpretReturnsTrueForNonFalseRepresentations()
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();

        $interpreter = new BooleanFieldInterpreter();

        $this->assertTrue($interpreter->interpret($configuration, true));
        $this->assertTrue($interpreter->interpret($configuration, 'true'));
        $this->assertTrue($interpreter->interpret($configuration, '1'));
        $this->assertTrue($interpreter->interpret($configuration, 1));
        $this->assertTrue($interpreter->interpret($configuration, 'test'));
        $this->assertTrue($interpreter->interpret($configuration, 123456789));
    }

    public function testInterpretReturnsNullForNull()
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();

        $interpreter = new BooleanFieldInterpreter();

        $this->assertNull($interpreter->interpret($configuration, null));
    }
}
