<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;

class LookupFieldInterpreter implements FieldInterpreterInterface
{
    protected $repositories;

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
        return $repository
            ->findOneBy([
                $fieldConfiguration->getLookupField() => $value
            ]);
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