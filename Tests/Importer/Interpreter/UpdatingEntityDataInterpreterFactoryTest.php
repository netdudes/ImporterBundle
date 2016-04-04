<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\UpdatingEntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\UpdatingEntityDataInterpreterFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdatingEntityDataInterpreterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $configuration = $this->prophesize(EntityConfigurationInterface::class)->reveal();
        $entityManager = $this->prophesize(EntityManager::class);
        $validator = $this->prophesize(ValidatorInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $factory = new UpdatingEntityDataInterpreterFactory($entityManager->reveal(), $validator->reveal(), $eventDispatcher->reveal());

        $interpreter = $factory->create($configuration);

        $this->assertInstanceOf(UpdatingEntityDataInterpreter::class, $interpreter);
    }
}
