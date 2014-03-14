<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BootstrapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add a custom block template in sonatra_block.twig.resources.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockTemplatePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('sonatra_block');
        $resources = $container->getParameter('sonatra_block.twig.resources');
        $offset = count($resources);

        if (isset($configs[0]['block']['resources'])) {
            $configResources = $configs[0]['block']['resources'];

            $offset = array_search($configResources[count($configResources) - 1], $resources);
        }

        array_splice($resources, $offset, 0, array(
            'SonatraBootstrapBundle:Block:block_bootstrap.html.twig',
            'SonatraBootstrapBundle:Block:component_bootstrap.html.twig',
        ));

        $container->setParameter('sonatra_block.twig.resources', $resources);
    }
}