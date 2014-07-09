<?php

namespace Netdudes\ImporterBundle\Tests\Importer\Interpreter\Field;

class LookupFieldInterpreterTest extends \PHPUnit_Framework_TestCase
{
    private function getMockRepository($entity)
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy'])
            ->getMock();

        $self = $this;
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue('SOME_ENTITY'));
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));
    }

    public function testInterpret()
    {
        return;
    }

}
