<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Importer
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * The current working directory for looking up files
     * @var string
     */
    protected $cwd;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * You can pass either the filename, a configuration according to the file or an array of files and an array of
     * configurations for the files. Mention, that the files and the configurations have to be in the same order.
     * The last parameter is primary optional, but if set, every filepath in front of each file will NOT be recognised.
     *
     * @param string|array $files
     * @param array        $arrayConfiguration
     * @param string       $currentWorkingDirectory
     *
     * @throws FileNotFoundException
     */
    public function import($files, array $arrayConfiguration, $currentWorkingDirectory = '')
    {
        $configurationReader = new YamlConfigurationReader();
        $configurationReader->read($arrayConfiguration);
        $configuration = $configurationReader->getConfigurationCollection();

        $importer = new CsvImporter($configuration, $this->entityManager);

        foreach ($files as $index => $file) {
            $key = array_keys($configuration->all())[$index];
            $importer->import($key, file_get_contents($file));
        }
    }

    /**
     * @param string $file
     * @param array  $configuration
     * @param string $currentWorkingDirectory
     *
     * @return bool
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @deprecated
     */
    protected function prepareAndRunImport($file, array $configuration, $currentWorkingDirectory)
    {
        /** Check if the "master" working directory is set and if yes, set it */
        if (!empty($currentWorkingDirectory)) {
            $this->setCwd($currentWorkingDirectory);
        } else {
            /** Set the "master" working directory from the filepath */
            $this->setCwd(dirname($file));
            /** Remove the path from the filename */
            $file = str_replace(dirname($file) . DIRECTORY_SEPARATOR, '', $file);
        }

        /** Build up the filepath and filename */
        $filename = $this->getCwd() . DIRECTORY_SEPARATOR . $file;

        /**
         * If the file exists, get the CSV-Data from it, otherwise return false and ignore the file
         */
        if (file_exists($filename)) {
            $csvData = $this->getCsvData($filename);
            if (!$csvData) {
                return false;
            }
            $this->importData($csvData, $configuration);
        } else {
            throw new FileNotFoundException('The file "' . $filename . '" could not be found.');
        }
    }

    /**
     * @return string
     * @deprecated
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    /**
     * @param string $cwd
     *
     * @return Importer
     * @deprecated
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return array|bool
     * @deprecated
     */
    protected function getCsvData($file)
    {
        /** Check if the file is empty or not */
        $fileContents = file_get_contents($file);
        if (strlen($fileContents) == 0) {
            return false;
        }

        /** Get the file contents per line from csv format */
        $file = new \SplFileObject($file);
        $csvData = array();
        while (!$file->eof()) {
            $csvData[] = array_map('stripslashes', $file->fgetcsv());
        }

        return $csvData;
    }

    /**
     * The actual method, that gets all required information and then inserts the entities into the db
     *
     * @param array $data
     * @param array $configuration
     */
    public function importData($data, $configuration)
    {
        /** Get the header row */
        $csvColumnHeaders = $this->parseDataForCsvHeaders($data, $configuration);

        $entities = array();

        /** Look into the configuration and find out, if we have a joinedImport case */
        if (isset($configuration['type'])) {
            /** In this part the application will identify, if we are dealing with a special import */
            switch ($configuration['type']) {
                case 'joinedImport':
                    /**
                     * In this case we have one entity, called the owner and one entity which is inserted into the owner.
                     * Example: Masterfile units (we need to add units to a specific masterfile)
                     */
                    $entities = $this->getJoinedEntitiesFromConfiguration($data, $csvColumnHeaders, $configuration);
                    break;
            }
        } else {
            /** Get all entities from given configuration */
            $entities = $this->getEntitiesFromConfiguration($data, $csvColumnHeaders, $configuration);
        }

        /** Insert the entities into the database */
        foreach ($entities as $entity) {
            $this->getEm()->persist($entity);
        }

        $this->getEm()->flush();
    }

    /**
     * @param array $data
     * @param array $configuration
     *
     * @return boolean|array
     * @throws \Exception
     * @deprecated
     */
    public function parseDataForCsvHeaders($data, $configuration)
    {
        /** Get the first row from the data (assuming that this is the header row) */
        $csvColumnHeaders = array_shift($data);
        $headerCount = count($csvColumnHeaders);
        /** Check that the file has a header column */
        $headerNotFoundCounter = 0;
        foreach ($csvColumnHeaders as $header) {
            if (!array_key_exists($header, $configuration['columns']) || is_numeric($header)) {
                $headerNotFoundCounter++;
            }
        }

        /**
         * If headers not found in the configuration throw an exception
         */
        if ($headerNotFoundCounter != 0 && $headerNotFoundCounter != $headerCount) {
            throw new \Exception('There are headers in the file, that are not configured.');
        } else {
            /**
             * In this case no headers are set and we don´t need a header row
             */
            if ($headerNotFoundCounter == $headerCount) {
                return false;
            }
        }

        /** Check if any row has a different count than the headers */
        foreach ($data as $rowNumber => $row) {
            if (count($row) != $headerCount) {
                throw new \Exception('Row number "' . ($rowNumber + 1) . '" has a different count than the headers.');
            }
        }

        return $csvColumnHeaders;
    }

    /**
     * Special case of importing. We have a joinedTable and have to add some entities to a specific set of entities.
     * We know in this method, that we are dealing with two different entites and so this method is limited to
     * two columns in the csv file. If there are more, they will be ignored.
     *
     * @param $data
     * @param $csvColumnHeaders
     * @param $configuration
     *
     * @return array
     * @throws \Exception
     * @deprecated
     */
    public function getJoinedEntitiesFromConfiguration($data, $csvColumnHeaders, $configuration)
    {
        $entities = array();
        /** Get the entity class from the configuration */
        $ownerEntityClass = $configuration['owner'];
        /** Create a reflectionClass for the given entity class */
        $entityReflectionClass = new \ReflectionClass($ownerEntityClass);
        if ($csvColumnHeaders) {
            /** Remove the headers from the data */
            array_shift($data);
            /** Transform the data array to a named array */
            $data = $this->numericToNamed($data, $csvColumnHeaders);
        }

        $internalOwnerLookupStorage = array();

        /** Loop through the rows */
        foreach ($data as $rowNumber => $row) {

            /** Get the index of both columns */
            $columnKeys = array_keys($row);

            /** Get the configuration for each column */
            $ownerColumnConfig = $configuration['columns'][$columnKeys[0]];
            $inversedColumnConfig = $configuration['columns'][$columnKeys[1]];

            /** Get the lookupProperty */
            $ownerLookupProperty = $ownerColumnConfig['lookupProperty'];
            $inversedLookupProperty = $inversedColumnConfig['lookupProperty'];

            /** Get the type */
            $ownerLookupType = $ownerColumnConfig['type'];
            $inversedLookupType = $inversedColumnConfig['type'];

            /** Get the data */
            $ownerColumnData = $row[$columnKeys[0]];
            $inversedColumnData = $row[$columnKeys[1]];

            /** Lookup the owner and if it was not cached, write it to the cache */
            $owner = null;
            if (array_key_exists($ownerColumnData, $internalOwnerLookupStorage)) {
                $owner = $internalOwnerLookupStorage[$ownerColumnData];
            } else {
                $owner = $this->lookupEntity($ownerLookupType, $ownerLookupProperty, $ownerColumnData);
                $internalOwnerLookupStorage[$ownerColumnData] = $owner;
            }

            /** Lookup the inversed entity */
            $inversedEntity = $this->lookupEntity($inversedLookupType, $inversedLookupProperty, $inversedColumnData);

            /** call the owner callback with the inversed entity as parameter */
            $ownerCallbackMethod = $inversedColumnConfig['ownerCallback'];
            if ($entityReflectionClass->hasMethod($ownerCallbackMethod)) {
                /** Call the ownerCallback method on the owner with the inversed entity as parameter */
                $owner->{$ownerCallbackMethod}($inversedEntity);
            } else {
                throw new \Exception("Class '{$entityReflectionClass->getName()}' has no method '$ownerCallbackMethod' (ownerCallback).");
            }

            $entities[] = $owner;
        }

        return $entities;
    }

    /**
     * Changes a numeric array into a named array
     *
     * @param array $data
     * @param array $headers
     *
     * @return array
     * @deprecated
     */
    public function numericToNamed($data, $headers)
    {
        $mappedData = array();
        foreach ($data as $rowNumber => $row) {
            foreach ($row as $cellIndex => $cell) {
                $mappedData[$rowNumber][$headers[$cellIndex]] = $cell;
            }
        }

        return $mappedData;
    }

    /**
     * Returns an entity for the given property = value where statement
     *
     * @param string $entityName
     * @param string $property
     * @param mixed  $value
     *
     * @return object
     * @throws \Exception
     * @deprecated
     */
    public function lookupEntity($entityName, $property, $value)
    {
        if (is_null($value) || empty($value) || strtolower($value) === 'null') {
            return null;
        }

        $repo = $this->getEm()->getRepository($entityName);
        $entity = $repo->findOneBy(
            [
                $property => $value
            ]
        );

        if (!$entity) {
            throw new \Exception("Entity with property '$property' and value '$value' not found.");
        }

        return $entity;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     * @deprecated
     */
    public function getEm()
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     *
     * @return Importer
     * @deprecated
     */
    public function setEm(EntityManager $em)
    {
        $this->entityManager = $em;

        return $this;
    }

    /**
     * @param array         $data
     * @param array|boolean $csvColumnHeaders
     * @param array         $configuration
     *
     * @return array
     * @throws \Exception
     * @deprecated
     */
    public function getEntitiesFromConfiguration($data, $csvColumnHeaders, $configuration)
    {
        $entities = array();
        /** Get the entity class from the configuration */
        $entityClass = $configuration['entity'];
        /** Create a reflectionClass for the given entity class */
        $entityReflectionClass = new \ReflectionClass($entityClass);
        if ($csvColumnHeaders) {
            /** Remove the headers from the data */
            array_shift($data);
            /** Transform the data array to a named array */
            $data = $this->numericToNamed($data, $csvColumnHeaders);
        }

        /**
         * The internalLookupStorage is used for looking up entities inside of one import loop for one table.
         * This is because we don´t need a separate db lookup for each data-record.
         */
        $internalLookupStorage = array();
        /** Loop through each row */
        foreach ($data as $rowNumber => $row) {
            /** Instantiate the given entityClass */
            $entityToInsert = new $entityClass();
            /** Loop through the columns */
            foreach ($row as $columnIndex => $columnData) {
                /** Check if the column exists inside the configuration */
                if (array_key_exists($columnIndex, $configuration['columns'])) {
                    /** Get the actual column configuration */
                    $column = $configuration['columns'][$columnIndex];
                    /** This property is used to identify, which property on the object has to be set */
                    $property = $column['property'];
                    /** Generate the setter method */
                    $setter = 'set' . ucfirst($property);
                    /** Check if the setter method exists */
                    if ($entityReflectionClass->hasMethod($setter)) {
                        /** If the cell-value is empty, set it to NULL */
                        if (strlen($columnData) > 0) {
                            /** If the type property is set, then go deeper into functionality */
                            if (isset($column['type'])) {
                                try {
                                    $type = $column['type'];
                                    switch ($type) {
                                        case 'date':
                                            /**
                                             * Convert the given date string into a DateTime object
                                             */
                                            $readFormat = (isset($column['read_format']) ? $column['read_format'] : 'Y-m-d');
                                            $columnData = $this->getDateTimeObject($columnData, $readFormat);
                                            break;
                                        case 'datetime':
                                            /**
                                             * Convert the given date string into a DateTime object with a different format
                                             */
                                            $readFormat = (isset($column['read_format']) ? $column['read_format'] : 'Y-m-d H:i:s');
                                            $columnData = $this->getDateTimeObject($columnData, $readFormat);
                                            break;
                                        case 'file':
                                            /** Read the content of the given file */
                                            $pathPrefix = $this->getCwd() . DIRECTORY_SEPARATOR;
                                            if (isset($column['pathPrefix'])) {
                                                $pathPrefix = $column['pathPrefix'];
                                            }
                                            $columnData = $this->getFileContent($columnData, $pathPrefix);
                                            break;
                                        default:
                                            if (!isset($column['lookupProperty'])) {
                                                throw new \Exception("You have to specify a lookup property in the configuration for type: " . $column['type']);
                                            }
                                            /**
                                             * If the given columnData exists in the internalLookupStorage as a key,
                                             * use the value of it. This eliminates no necessary db operations.
                                             */
                                            if (array_key_exists($columnData, $internalLookupStorage)) {
                                                $columnData = $internalLookupStorage[$columnData];
                                            } else {
                                                $columnData = $this->lookupEntity(
                                                    $type,
                                                    $column['lookupProperty'],
                                                    $columnData
                                                );
                                            }
                                            /**
                                             * If the 'success' property is given, we go inside a deeper callstack
                                             * The method in 'call' will be called on the initial entity and the method
                                             * in 'with' will be called on the looked up entity. The result of the 'with'
                                             * method will be used as a parameter for the method in 'call'
                                             */
                                            if (isset($column['success'])) {
                                                $callStack = $column['success'];
                                                $lookupReflectionClass = new \ReflectionClass($columnData);
                                                if ($entityReflectionClass->hasMethod(
                                                        $callStack['call']
                                                    ) && $lookupReflectionClass->hasMethod($callStack['with'])
                                                ) {
                                                    $entityToInsert->{$callStack['call']}(
                                                        $columnData->{$callStack['with']}()
                                                    );
                                                } else {
                                                    throw new \Exception('The looked up entity does not have a method "' . $column['callback'] . '"');
                                                }
                                            }
                                            break;
                                    }
                                } catch (\Exception $e) {
                                    throw new \Exception('Error in CSV line "' . ($rowNumber + 1) . '" with message "' .
                                        $e->getMessage() . '". ColumnProperty: ' . json_encode(
                                            $column
                                        ) . '; Entity: ' . $entityClass);
                                }
                            } else {
                                $columnData = trim($columnData);
                            }
                        } else {
                            $columnData = null;
                        }
                        /**
                         * Call the setter method with the given column data
                         */
                        $entityToInsert->{$setter}($columnData);
                    }
                }
            }

            /**
             * The innerLookupProperty defines, which value from the entity will be used as the key for the
             * internalLookupStorage. In future this logic will be extended to use cross-table lookups while not using
             * the database at all.
             */
            if (isset($configuration['innerLookupProperty'])) {
                $getter = 'get' . ucfirst($configuration['innerLookupProperty']);
                if ($entityReflectionClass->hasMethod($getter)) {
                    $internalLookupStorage[$entityToInsert->{$getter}()] = $entityToInsert;
                }
            }

            $entities[] = $entityToInsert;

        }

        return $entities;
    }

    /**
     * Returns a DateTime object for the given value and format
     *
     * @param string $value
     * @param string $readFormat
     *
     * @return \DateTime
     * @throws \Exception
     * @deprecated
     */
    public function getDateTimeObject($value, $readFormat = 'Y-m-d')
    {
        $convertedValue = \DateTime::createFromFormat($readFormat, $value);
        if (!$convertedValue) {
            throw new \Exception("The given date '$value' could not be converted. Maybe the read format is not correct: '$readFormat'");
        }

        return $convertedValue;
    }

    /**
     * Reads the content of a given file
     *
     * @param string $filename
     * @param string $pathPrefix
     *
     * @return string
     * @throws \Exception
     * @deprecated
     */
    public function getFileContent($filename, $pathPrefix = '')
    {
        $filePath = $pathPrefix . $filename;
        if (file_exists($filePath)) {
            $fileContents = file_get_contents($filePath);
        } else {
            throw new \Exception("File with name '$filePath' could not be found.");
        }

        return $fileContents;
    }

}
