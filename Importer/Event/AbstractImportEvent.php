<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\ImporterInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractImportEvent extends Event
{
    /**
     * @var ImporterInterface
     */
    public $importer;

    public function __construct(ImporterInterface $importer)
    {
        $this->importer = $importer;
    }
}