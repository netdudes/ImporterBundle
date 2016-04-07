<?php

namespace Netdudes\ImporterBundle\Yaml;

use Symfony\Component\Yaml\Parser;

class YamlParserFactory
{
    /**
     * @return Parser
     */
    public function create()
    {
        return new Parser();
    }
}
