<?php
namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\BooleanFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidValueException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\BooleanFieldInterpreter;

class BooleanFieldInterpreterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValidBooleanValues
     *
     * @param mixed $value
     * @param bool  $result
     */
    public function testInterpretReturnsTrueOrFalseForValidBooleanOptions($value, $result)
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();
        $interpreter = new BooleanFieldInterpreter();

        $this->assertEquals($result, $interpreter->interpret($configuration, $value));
    }

    /**
     * @return array
     */
    public function provideValidBooleanValues()
    {
        return [
            [false, false],
            ['False', false],
            ['false', false],
            ['No', false],
            ['no', false],
            ['0', false],
            [0, false],
            [true, true],
            ['True', true],
            ['true', true],
            ['Yes', true],
            ['yes', true],
            ['1', true],
            [1, true]
        ];
    }

    /**
     * @dataProvider provideInvalidValuesForBooleanField
     *
     * @param mixed $value
     */
    public function testInterpreterThrowsBooleanFieldExceptionForInvalidValue($value)
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();
        $interpreter = new BooleanFieldInterpreter();

        $this->setExpectedExceptionRegExp(InvalidValueException::class);

        $interpreter->interpret($configuration, $value);
    }

    /**
     * @return array
     */
    public function provideInvalidValuesForBooleanField()
    {
        return [
            ["'False'"],
            ["'false'"],
            ["'No'"],
            ["'no'"],
            ['0.0'],
            ["'True'"],
            ["'true'"],
            ["'Yes'"],
            ["'yes'"],
            ['1.0'],
            [2]
        ];
    }

    public function testInterpretReturnsNullForNull()
    {
        $configuration = $this->prophesize(BooleanFieldConfiguration::class)->reveal();
        $interpreter = new BooleanFieldInterpreter();

        $this->assertNull($interpreter->interpret($configuration, null));
    }
}
