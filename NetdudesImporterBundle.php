<?php

namespace Netdudes\ImporterBundle;

use Netdudes\ImporterBundle\DependencyInjection\CompilerPass\ImportEventSubscribersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetdudesImporterBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ImportEventSubscribersCompilerPass());
    }
}
