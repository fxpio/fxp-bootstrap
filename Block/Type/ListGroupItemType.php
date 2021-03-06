<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Bootstrap\Block\Type;

use Fxp\Component\Block\AbstractType;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * List Group Item Block Type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ListGroupItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $attr = $options['attr'];

        if (null !== $options['src']) {
            $attr['href'] = $options['src'];
        }

        $view->vars = array_replace($view->vars, [
            'attr' => $attr,
            'active' => $options['active'],
            'is_link' => null !== $options['src'],
            'style' => $options['style'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'src' => null,
            'active' => false,
            'style' => null,
        ]);

        $resolver->setAllowedTypes('src', ['null', 'string']);
        $resolver->setAllowedTypes('active', 'bool');
        $resolver->setAllowedTypes('style', ['null', 'string']);

        $resolver->setAllowedValues('style', [null, 'success', 'info', 'warning', 'danger']);

        $resolver->setNormalizer('src', function (Options $options, $value = null) {
            if (isset($options['data'])) {
                return $options['data'];
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ListItemType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'list_group_item';
    }
}
