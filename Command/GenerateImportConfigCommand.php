<?php
namespace Netdudes\ImporterBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateImportConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('importer:generate:config')
            ->setDescription('Generate a basic import configuration.')
            ->addOption('name', 'cn', InputOption::VALUE_REQUIRED, 'Enter the name of the configuration')
            ->addOption('entity', 'en', InputOption::VALUE_REQUIRED, 'Enter the name of the entity');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * 
     * @throws \Exception
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');
        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, '<question>Do you confirm generation? Y/[N]</question>', true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (['name', 'entity'] as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        $registry = $this->getContainer()->get('doctrine');
        $entityManager = $registry->getManager();

        $entityClass = $registry->getAliasNamespace($bundle->getName()) . '\\' . $entity;
        $entityPath = $bundle->getPath() . '/Entity/' . str_replace('\\', '/', $entity) . '.php';
        if (!file_exists($entityPath)) {
            throw new \RuntimeException(sprintf('Entity "%s" not found.', $entityClass));
        }

        $classMetaData = $entityManager->getClassMetadata($entityClass);
        $properties = $classMetaData->getReflectionClass()->getProperties();
        $identifiers = $classMetaData->getIdentifierFieldNames();

        $propertiesToGenerate = [];
        foreach ($properties as $index => $property) {
            $propertyName = $property->getName();
            if (!in_array($propertyName, $identifiers)) {
                $propertiesToGenerate[$index] = [];
                $propertiesToGenerate[$index]['property'] = $propertyName;
                if (in_array($classMetaData->getTypeOfField($propertyName), ['date', 'datetime'])) {
                    $propertiesToGenerate[$index]['type'] = $classMetaData->getTypeOfField($propertyName);
                }
                if ($classMetaData->hasAssociation($propertyName)) {
                    $propertiesToGenerate[$index]['type'] = $classMetaData->getAssociationTargetClass($propertyName);
                    $propertiesToGenerate[$index]['lookupProperty'] = 'INSERT_PROPERTY_HERE';
                }
            }
        }

        /**
         * Generate the default configuration structure
         */
        $newConfigName = $input->getOption('name');
        $configuration = [
            $newConfigName => [
                'entity' => $entityClass,
                'columns' => $propertiesToGenerate,
            ],
        ];

        $configurationPath = $bundle->getPath() .
            DIRECTORY_SEPARATOR .
            'Resources' .
            DIRECTORY_SEPARATOR .
            'config' .
            DIRECTORY_SEPARATOR .
            'imports.yml';

        if (!file_exists($configurationPath)) {
            throw new \Exception("Configuration '$configurationPath' not found.");
        }

        /**
         * Read the current configuration and parse it
         */
        $currentConfigurationFileContent = file_get_contents($configurationPath);
        $currentConfiguration = Yaml::parse($currentConfigurationFileContent);

        if (array_key_exists($newConfigName, $currentConfiguration)) {
            throw new \Exception("'$newConfigName' configuration already exists.");
        }

        $newConfiguration = array_merge($currentConfiguration, $configuration);

        /* Sort entries via name ASC */
        ksort($newConfiguration);

        $newConfigurationYaml = Yaml::dump($newConfiguration, 4, 2);

        if ($dialog->askConfirmation($output, '<question>Do you want to preview your configuration? Y/[N]</question>', true)) {
            $formatter = $this->getHelperSet()->get('formatter');
            $configPreview = [$newConfigurationYaml];
            $formattedBlock = $formatter->formatBlock($configPreview, 'info', true);
            $output->writeln($formattedBlock);
        }

        if (!$dialog->askConfirmation($output, '<question>Do you want to write the new configuration? Y/[N]</question>', true)) {
            $output->writeln('<error>Command aborted</error>');

            return 1;
        }

        file_put_contents($configurationPath, Yaml::dump($newConfiguration, 4, 2));

        $output->writeln('<info>Configuration created.</info>');
    }

    /**
     * @param string $shortcut
     * 
     * @return array
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)',
                $entity
            ));
        }

        return [substr($entity, 0, $pos), substr($entity, $pos + 1)];
    }
}
