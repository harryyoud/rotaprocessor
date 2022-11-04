<?php

namespace App\DependencyInjection\Compiler;

use App\SheetParsers\SheetParsers;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SheetParserPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        if (!$container->has(SheetParsers::class)) {
            return;
        }
        $definition = $container->findDefinition(SheetParsers::class);
        $taggedServices = $container->findTaggedServiceIds('app.sheet_parser');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addParser', [new Reference($id)]);
        }
    }
}