<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;

class LookupFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @var EntityRepository[]
     */
    protected $repositories = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var null
     */
    private $internalLookupCache;

    /**
     * @param EntityManager $entityManager
     * @param array|null    $internalLookupCache
     */
    public function __construct(EntityManager $entityManager, array &$internalLookupCache = null)
    {
        $this->entityManager = $entityManager;
        $this->internalLookupCache = &$internalLookupCache;
    }

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param mixed                       $value
     *
     * @throws LookupFieldException
     *
     * @return mixed
     */
    public function interpret(FieldConfigurationInterface $fieldConfiguration, $value)
    {
        if (!($fieldConfiguration instanceof LookupFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        if (empty(trim($value))) {
            return null;
        }

        $class = $fieldConfiguration->getClass();
        $repository = $this->getRepository($class);
        try {
            $queryLookupField = $fieldConfiguration->getLookupField();
            $entityId = $repository
                ->createQueryBuilder('e')
                ->select('e.id id')
                ->where("e.$queryLookupField = :value")
                ->setParameter('value', $value)
                ->getQuery()
                ->getScalarResult();
            if (count($entityId) == 0) {
                throw new NoResultException();
            }

            return $this->entityManager->getReference($class, $entityId[0]);
        } catch (NoResultException $exception) {
            $internalLookupEntity = $this->internalLookup($class, $queryLookupField, $value);
            if (is_null($internalLookupEntity)) {
                throw $this->buildLookupFieldException($fieldConfiguration, $value, $exception);
            }

            return $internalLookupEntity;
        } catch (ORMException $exception) {
            throw $this->buildLookupFieldException($fieldConfiguration, $value, $exception);
        }
    }

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param mixed                       $value
     * @param \Exception                  $exception
     *
     * @return LookupFieldException
     */
    protected function buildLookupFieldException(FieldConfigurationInterface $fieldConfiguration, $value, $exception)
    {
        $exception = new LookupFieldException("Error when trying to find entity of class \"{$fieldConfiguration->getClass()}\" for property \"{$fieldConfiguration->getLookupField()}\" with value \"{$value}\"", 0, $exception);
        $exception->setValue($value);
        $exception->setFieldConfiguration($fieldConfiguration);

        return $exception;
    }

    /**
     * @param string $class
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

    /**
     * @param string $class
     * @param string $lookupField
     * @param mixed  $value
     *
     * @return null|object
     */
    private function internalLookup($class, $lookupField, $value)
    {
        if (is_null($this->internalLookupCache)) {
            return null;
        }
        foreach ($this->internalLookupCache as $entity) {
            if (get_class($entity) !== $class) {
                continue;
            }
            $getter = 'get' . ucfirst($lookupField);
            if (!method_exists($entity, $getter)) {
                return null;
            }
            if ($entity->{$getter}() == $value) {
                return $entity;
            }
        }

        return null;
    }
}
