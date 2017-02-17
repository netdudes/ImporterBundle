<?php
namespace Netdudes\ImporterBundle\Tests\Importer\Parser;

use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Netdudes\ImporterBundle\Importer\Parser\Exception\ParserException
     */
    public function testDifferentRowLengthThrowsParserException()
    {
        $csvParser = new CsvParser();

        $data = "Column1,Column2\nColumn1Value";

        $csvParser->parse($data);
    }
}
