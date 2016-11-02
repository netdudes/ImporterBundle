<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;

class DatetimeFieldInterpreterTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpretWithNoFormat()
    {
        $configuration = new DateTimeFieldConfiguration();
        $configuration->setField('test_field');
        $value = '1999-02-03 11:22:33';

        $interpreter = new DatetimeFieldInterpreter();
        $dateTime = $interpreter->interpret($configuration, $value);

        $this->assertEquals('1999', $dateTime->format('Y'));
        $this->assertEquals('2', $dateTime->format('m'));
        $this->assertEquals('03', $dateTime->format('d'));
        $this->assertEquals('11', $dateTime->format('H'));
        $this->assertEquals('22', $dateTime->format('i'));
        $this->assertEquals('33', $dateTime->format('s'));
    }

    public function testInterpretWithFormat()
    {
        $configuration = new DateTimeFieldConfiguration();
        $configuration->setField('test_field');
        $configuration->setFormat('i=H^s d//m))Y');
        $value = '22=11^33 03//02))1999';

        $interpreter = new DatetimeFieldInterpreter();
        $dateTime = $interpreter->interpret($configuration, $value);

        $this->assertEquals('1999', $dateTime->format('Y'));
        $this->assertEquals('2', $dateTime->format('m'));
        $this->assertEquals('03', $dateTime->format('d'));
        $this->assertEquals('11', $dateTime->format('H'));
        $this->assertEquals('22', $dateTime->format('i'));
        $this->assertEquals('33', $dateTime->format('s'));
    }

    public function testInterpretDatetimeFieldWithoutTimeData()
    {
        $configuration = new DateTimeFieldConfiguration();
        $value = '2015-10-12';

        $interpreter = new DatetimeFieldInterpreter();
        $dateTime = $interpreter->interpret($configuration, $value);

        $this->assertEquals('2015', $dateTime->format('Y'));
        $this->assertEquals('10', $dateTime->format('m'));
        $this->assertEquals('12', $dateTime->format('d'));
        $this->assertEquals('00', $dateTime->format('H'));
        $this->assertEquals('00', $dateTime->format('i'));
        $this->assertEquals('00', $dateTime->format('s'));
    }
}
