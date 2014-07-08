<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

class AbstractInterpreter
{



    protected abstract function interpretAssociativeRow($columns);
    protected abstract function interpretOrderedRow($row);


} 