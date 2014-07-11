<?php

namespace Netdudes\ImporterBundle\Yaml;

use Symfony\Component\Yaml\Parser;

class YamlParserFactory
{
    public function create()
    {
        return new Parser();
    }
}
