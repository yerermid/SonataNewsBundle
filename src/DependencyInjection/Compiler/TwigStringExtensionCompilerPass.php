<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder,Definition};
use Twig\Extra\String\StringExtension;

final class TwigStringExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('twig.extension') as $id => $attributes) {
            if (StringExtension::class === $container->getDefinition($id)->getClass()) {
                return;
            }
        }

        $definition = new Definition(StringExtension::class);
        $definition->addTag('twig.extension');
        $container->setDefinition(StringExtension::class, $definition);
    }
}
