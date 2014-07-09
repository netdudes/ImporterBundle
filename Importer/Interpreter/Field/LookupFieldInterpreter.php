<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;

class LookupFieldInterpreter implements FieldInterpreterInterface
{
    protected $repositories = [];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function interpret(FieldConfigurationInterface $fieldConfiguration, $value)
    {
        if (!($fieldConfiguration instanceof LookupFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        $class = $fieldConfiguration->getClass();
        $repository = $this->getRepository($class);
        try {
            return $repository
                ->findOneBy([
                    $fieldConfiguration->getLookupField() => $value
                ]);
        } catch (ORMException $exception) {
            $exception = new LookupFieldException("Error when trying to find entity of class \"{$fieldConfiguration->getClass()}\" for property \"{$fieldConfiguration->getLookupField()}\" with value \"{$value}\"", 0, $exception);
            $exception->setValue($value);
            $exception->setFieldConfiguration($fieldConfiguration);
            throw $exception;
        }
    }

    /**
     * @param $class
     *
     * @return EntityRepository
     */
    private function getRepository($class)
    {
        if (!array_key_exists($class, $this->repositories)) {
            $this->repositories[$class] = $this->entityManager->getRepository($class);
        }

        return $this->repositories[$class];
    }

} 