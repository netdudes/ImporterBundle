<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationFactory;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration;
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
        $reader = new YamlConfigurationReader(new Parser(), new FieldConfigurationFactory());
        $configurationCollection = $reader->readFile($this->getTestEntityConfigurationFileName());

        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface', $configurationCollection, 'A reader should return a configuration collection');
        $this->assertCount(1, $configurationCollection, 'One configuration node should be available');
        $this->assertNotNull($configurationCollection->get('test_entity'), 'A configuration node with key test_entity should exist');

        $entityConfiguration = $configurationCollection->get('test_entity');
        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface', $entityConfiguration);
        $fields = $entityConfiguration->getFields();
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
        $reader = new YamlConfigurationReader(new Parser(), new FieldConfigurationFactory());
        $configurationCollection = $reader->readFile($this->getTestJoinedImportConfigurationFileName());

        $this->assertInstanceOf('Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface', $configurationCollection, 'A reader should return a configuration collection');
        $this->assertCount(1, $configurationCollection, 'One configuration node should be available');
        $this->assertNotNull($configurationCollection->get('test_joint'), 'A configuration node with key test_entity should exist');

        /** @var $joinedImportConfiguration \Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration */
        $joinedImportConfiguration = $configurationCollection->get('test_joint');
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
