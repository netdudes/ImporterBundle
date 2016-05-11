<?php

namespace Netdudes\ImporterBundle\Tests\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\AbstractImporter;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Prophecy\Argument;

class AbstractImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurationInterface
     */
    private $configurationProphecy;

    /**
     * @var EntityManager
     */
    private $entityManagerProphecy;

    /**
     * @var InterpreterInterface
     */
    private $interpreterProphecy;

    public function testItDetachesEntitiesWhenFlushIsFalseOnImportData()
    {
        $data = [];
        $dataIsAssociativeArray = true;
        $object = new \stdClass();

        $this->interpreterProphecy->interpret($data, $dataIsAssociativeArray)->shouldBeCalled()->willReturn([$object]);
        $this->entityManagerProphecy->persist($object)->shouldBeCalled();
        $this->entityManagerProphecy->detach($object)->shouldBeCalled();
        $this->entityManagerProphecy->flush()->shouldNotBeCalled();

        $testImporter = new TestImporter($this->configurationProphecy->reveal(), $this->interpreterProphecy->reveal(), $this->entityManagerProphecy->reveal());
        $testImporter->import($data, $dataIsAssociativeArray, false);
    }

    protected function setUp()
    {
        $this->configurationProphecy = $this->prophesize(ConfigurationInterface::class);
        $this->entityManagerProphecy = $this->prophesize(EntityManager::class);
        $this->interpreterProphecy = $this->prophesize(InterpreterInterface::class);
    }
}

class TestImporter extends AbstractImporter
{
    /**
     * @param array $data
     * @param bool  $dataIsAssociativeArray
     * @param bool  $flush
     *
     * @return null|\object[]
     * @throws DatabaseException
     */
    public function import($data, $dataIsAssociativeArray = true, $flush = true)
    {
        return $this->importData($data, $dataIsAssociativeArray, $flush);
    }

    /**
     * @param string $filename
     *
     * @return object[]
     */
    public function importFile($filename)
    {
    }
}