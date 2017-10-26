<?php

namespace Shopware\Traceable\DependencyInjection;

use Shopware\Traceable\Cart\CartCalculatorTracer;
use Shopware\Traceable\Cart\CollectorTracer;
use Shopware\Traceable\Cart\ProcessorTracer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CartTracerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('cart.processor');

        foreach ($services as $id => $tags) {
            $this->decorateService($container, $id, ProcessorTracer::class);
        }

        $services = $container->findTaggedServiceIds('cart.collector');
        foreach ($services as $id => $tags) {
            $this->decorateService($container, $id, CollectorTracer::class);
        }

        $definition = new Definition(
            CartCalculatorTracer::class,
            [
                new Reference('cart.calculator.tracer.inner'),
                new Reference('shopware.traceable.traced_cart_actions'),
            ]
        );
        $definition->setDecoratedService('cart.calculator');
        $container->setDefinition('cart.calculator.tracer', $definition);
    }

    protected function decorateService(ContainerBuilder $container, string $serviceId, string $class): void
    {
        $new = new Definition(ProcessorTracer::class, [
            new Reference($serviceId . '.tracer.inner'),
            new Reference('shopware.traceable.traced_cart_actions'),
        ]);

        $new->setDecoratedService($serviceId);
        $container->setDefinition($serviceId . '.tracer', $new);
    }
}