<?php

namespace Netdudes\ImporterBundle\Tests\Importer;

use Netdudes\ImporterBundle\Importer\Importer;

class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Run the importer with a given configuration and assume that it returns the correct entities
     * @covers Netdudes\ImporterBundle\Importer\Importer::getEntitiesFromConfiguration
     */
    public function testImporterReturnsTheExpectedEntitiesForTheConfiguration()
    {
        $entityManager = $this->getMockEntityManager();

        $configuration = $this->getConfiguration();

        $headers = ['Title', 'Author', 'Year'];

        $data = [
            [
                'Title',
                'Author',
                'Year'
            ],
            [
                'Das Kind',
                'Sebastian Fitzek',
                2010,
            ],
            [
                'Splitter',
                'Sebastian Fitzek',
                2011,
            ],
        ];

        $importer = new Importer($entityManager);

        $entities = $importer->getEntitiesFromConfiguration($data, $headers, $configuration);

        $this->assertCount(2, $entities);
        $this->assertInstanceOf('Netdudes\ImporterBundle\Tests\Importer\Book', $entities[0]);

    }

    public function getMockEntityManager()
    {
        $entityManager = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array(),
            array(),
            '',
            false
        );

        return $entityManager;
    }

    public function getConfiguration()
    {
        $configuration = [
            'entity' => 'Netdudes\ImporterBundle\Tests\Importer\Book',
            'columns' => [
                'a' => [
                    'property' => 'title',
                ],
                'b' => [
                    'property' => 'year',
                ],
                'c' => [
                    'property' => 'author',
                ],
            ],
        ];

        return $configuration;
    }

    /**
     * Test scenarios
     * 1. Headers set as normal
     * 2. Only some headers set and data count is correct
     * 3. Not valid header set
     * 3. Only some headers set and data count is not correct
     * 4. Headers not set
     * @testdox Check if the given headers are found
     * @covers Netdudes\ImporterBundle\Importer\Importer::parseDataForCsvHeaders
     */
    public function testHeadersAreFoundCorrectEitherAllOrJustOne()
    {
        $entityManager = $this->getMockEntityManager();

        $importer = new Importer($entityManager);

        $configuration = $this->getConfiguration();

        /** Data for scenario 1 */
        $data1 = json_decode(
            '[["a","b","c"],["Das Kind", 2011, "Sebastian Fitzek"],["Der Seelenbrecher", 2012, "Sebastian Fitzek"]]'
        );
        /** Data for scenario 2 */
        $data2 = json_decode('[["a"],["Das Kind"],["Splitter"]]');

        $headers1 = $importer->parseDataForCsvHeaders($data1, $configuration);

        $this->assertEquals('["a","b","c"]', json_encode($headers1));

        $headers2 = $importer->parseDataForCsvHeaders($data2, $configuration);

        $this->assertEquals('["a"]', json_encode($headers2));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Row number "1" has a different count than the headers.
     * @testdox The count of data being not equal to the headers should result in an exception
     * @covers Netdudes\ImporterBundle\Importer\Importer::parseDataForCsvHeaders
     */
    public function testDataCountIsNotEqualHeaderCount()
    {

        $entityManager = $this->getMockEntityManager();
        $importer = new Importer($entityManager);
        $configuration = $this->getConfiguration();

        /** Data for scenario 3 */
        $data = json_decode(
            '[["a","b"],["Das Kind", 2011, "Sebastian Fitzek"],["Der Seelenbrecher", 2012, "Sebastian Fitzek"]]'
        );

        $headers = $importer->parseDataForCsvHeaders($data, $configuration);
    }

    /**
     * @testdox If no headers given in the file, the headers return value should be false
     * @covers Netdudes\ImporterBundle\Importer\Importer::parseDataForCsvHeaders
     */
    public function testHeadersAreNotSetInFile()
    {
        $entityManager = $this->getMockEntityManager();
        $importer = new Importer($entityManager);
        $configuration = $this->getConfiguration();

        /** Data for scenario 4 */
        $data = json_decode('[["Das Kind", 2011, "Sebastian Fitzek"],["Der Seelenbrecher", 2012, "Sebastian Fitzek"]]');

        $headers = $importer->parseDataForCsvHeaders($data, $configuration);

        $this->assertFalse($headers);
    }

    /**
     * @testdox The given numeric array should end up being a named array
     * @covers Netdudes\ImporterBundle\Importer\Importer::numericToNamed
     */
    public function testNumbericArrayToNamed()
    {
        $entityManager = $this->getMockEntityManager();
        $importer = new Importer($entityManager);
        $configuration = $this->getConfiguration();

        $numericArray = [
            ["Das Kind", "Sebastian Fitzek", 2011]
        ];

        $namedArray = $importer->numericToNamed($numericArray, ["Title", "Author", "Year"]);

        $this->assertArrayHasKey('Title', $namedArray[0]);
    }

    /**
     * @testdox Assume, that the given string date value will be converted into a DateTime object
     * @covers Netdudes\ImporterBundle\Importer\Importer::getDateTimeObject
     */
    public function testValueIsConvertedToDateTimeObject()
    {
        $entityManager = $this->getMockEntityManager();
        $importer = new Importer($entityManager);
        $configuration = $this->getConfiguration();

        $date = '2012-04-12 01:01:01';
        $dateObject = $importer->getDateTimeObject($date, 'Y-m-d H:i:s');

        $this->assertInstanceOf('\DateTime', $dateObject);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage There are headers in the file, that are not configured.
     */
    public function testIfAHeaderIsSetInTheFileThatDoesNotExistThereIsAnException()
    {
        $entityManager = $this->getMockEntityManager();

        $importer = new Importer($entityManager);

        $configuration = $this->getConfiguration();

        /** Data for scenario 3 */
        $data = json_decode('[["a","q"],["Das Kind", 2012],["Splitter", 2011]]');

        $headers = $importer->parseDataForCsvHeaders($data, $configuration);
    }
}
