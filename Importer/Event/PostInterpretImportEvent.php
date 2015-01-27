<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\ImporterInterface;

class PostInterpretImportEvent extends AbstractImportEvent
{
    /**
     * @var object
     */
    public $entity;

    public function __construct($entity, ImporterInterface $importer)
    {
        parent::__construct($importer);
        $this->entity = $entity;
    }
}