<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Bootstrap\Block\Extension;

use Fxp\Component\Block\AbstractTypeExtension;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Extension\Core\Type\ObjectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Layout Block Extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class LayoutExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'layout' => $options['layout'],
            'layout_col_size' => $options['layout_col_size'],
            'layout_col_label' => $options['layout_col_label'],
            'layout_col_control' => $options['layout_col_control'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars = array_replace($child->vars, [
                'layout' => $view->vars['layout'],
                'layout_col_size' => $view->vars['layout_col_size'],
                'layout_col_label' => $view->vars['layout_col_label'],
                'layout_col_control' => $view->vars['layout_col_control'],
            ]);

            if ('inline' === $view->vars['layout']) {
                $child->vars['display_label'] = false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'layout' => null,
                'layout_col_size' => 'lg', // only for horizontal layout
                'layout_col_label' => 4, // only for horizontal layout
                'layout_col_control' => 8, // only for horizontal layout
            ]
        );

        $resolver->addAllowedTypes('layout', ['null', 'string']);
        $resolver->addAllowedTypes('layout_col_size', 'string');
        $resolver->addAllowedTypes('layout_col_label', 'int');
        $resolver->addAllowedTypes('layout_col_control', 'int');

        $resolver->addAllowedValues('layout', [null, 'inline', 'horizontal']);
        $resolver->addAllowedValues('layout_col_size', ['xs', 'sm', 'md', 'lg']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [ObjectType::class];
    }
}
