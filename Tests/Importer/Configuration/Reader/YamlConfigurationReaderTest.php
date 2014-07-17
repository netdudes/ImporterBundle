<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Symfony\Component\Yaml\Parser;

class YamlConfigurationReaderTest extends \PHPUnit_Framework_TestCase
{
    private function getTestEntityConfigurationFileName()
    {
        return __DIR__ . '/testEntityConfiguration.yml';
    }

    private function getTestJoinedImportConfigurationFileName()
    {
        return __DIR__ . '/testJoinedImportConfiguration.yml';
    }

    public function testReadEntityConfigurationYaml()
    {
        $reader = new YamlConfigurationReader(new Parser());
        $configuration = $reader->readFile($this->getTestEntityConfigurationFileName());

        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface', $configuration);
        $fields = $configuration->getFields();
        $this->assertCount(3, $fields, 'Three fields should be defined');

        $testLookupField = $fields['Test Lookup Column'];
        $testLiteralField = $fields['Test Literal Column'];
        $testDateTimeField = $fields['Test DateTime Column'];
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration', $testLookupField);
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Field\LiteralFieldConfiguration', $testLiteralField);
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration', $testDateTimeField);

        $this->assertEquals('\Path\To\Another\TestEntity', $testLookupField->getClass());
        $this->assertEquals('name', $testLookupField->getLookupField());

        $this->assertEquals('literalColumn', $testLiteralField->getField());

        $this->assertEquals('datetimeColumn', $testDateTimeField->getField());
        $this->assertEquals('d.m.Y H:i:s', $testDateTimeField->getFormat());
    }

    public function testReadJoinedImportConfigurationYaml()
    {
        $reader = new YamlConfigurationReader(new Parser());
        $joinedImportConfiguration = $reader->readFile($this->getTestJoinedImportConfigurationFileName());

        /** @var $joinedImportConfiguration \Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration */
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface', $joinedImportConfiguration);

        $this->assertEquals('theCallbackOnTheOwnerClass', $joinedImportConfiguration->getAssignmentMethod());
        $ownerLookupConfigurationField = $joinedImportConfiguration->getOwnerLookupConfigurationField();
        $relatedLookupConfigurationField = $joinedImportConfiguration->getRelatedLookupConfigurationField();
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration', $ownerLookupConfigurationField);
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration', $relatedLookupConfigurationField);
        $this->assertEquals('lookupPropertyOwnerClass', $ownerLookupConfigurationField->getLookupField());
        $this->assertEquals('\Namespace\Of\Owner\Class', $ownerLookupConfigurationField->getClass());
        $this->assertEquals('propertyOnTheOtherClass', $relatedLookupConfigurationField->getLookupField());
        $this->assertEquals('\Namespace\To\Other\Class', $relatedLookupConfigurationField->getClass());
    }

}
