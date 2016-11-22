<?php
namespace Netdudes\ImporterBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportEventSubscribersCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('netdudes_importer.event_dispatcher')) {
            return;
        }

        $eventDispatcher = $container->getDefinition('netdudes_importer.event_dispatcher');

        foreach ($container->findTaggedServiceIds('u2.importer.event_subscriber') as $id => $attributes) {
            $eventDispatcher->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}
