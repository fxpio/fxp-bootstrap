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
use Fxp\Component\Block\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Column Block Type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ColumnType extends AbstractType
{
    private $validPrefix = ['xs', 'sm', 'md', 'lg'];

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'grid' => $options['grid'],
            'offset' => $options['offset'],
            'push' => $options['push'],
            'pull' => $options['pull'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'grid' => 'md-1',
            'offset' => null,
            'push' => null,
            'pull' => null,
        ]);

        $resolver->setAllowedTypes('grid', ['string', 'array']);
        $resolver->setAllowedTypes('offset', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('push', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('pull', ['null', 'string', 'array']);

        $resolver->setNormalizer('grid', function (Options $options, $value = null) {
            $value = $this->convertToArray($value);

            foreach ($value as $i => $grid) {
                list($prefix, $size) = $this->getParams('grid', $grid);

                $value[$i] = sprintf('col-%s-%s', $prefix, $size);
            }

            return $value;
        });
        $resolver->setNormalizer('offset', function (Options $options, $value = null) {
            $value = $this->convertToArray($value);

            foreach ($value as $i => $offset) {
                list($prefix, $size) = $this->getParams('offset', $offset);

                $value[$i] = sprintf('col-%s-offset-%s', $prefix, $size);
            }

            return $value;
        });
        $resolver->setNormalizer('push', function (Options $options, $value = null) {
            $value = $this->convertToArray($value);

            foreach ($value as $i => $push) {
                list($prefix, $size) = $this->getParams('push', $push);

                $value[$i] = sprintf('col-%s-push-%s', $prefix, $size);
            }

            return $value;
        });
        $resolver->setNormalizer('pull', function (Options $options, $value = null) {
            $value = $this->convertToArray($value);

            foreach ($value as $i => $pull) {
                list($prefix, $size) = $this->getParams('pull', $pull);

                $value[$i] = sprintf('col-%s-pull-%s', $prefix, $size);
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'col';
    }

    /**
     * Convert value to array.
     *
     * @param array|string|null $value
     *
     * @return array
     */
    protected function convertToArray($value)
    {
        if (\is_string($value)) {
            $value = [$value];
        } elseif (null === $value) {
            $value = [];
        }

        return $value;
    }

    /**
     * Get the option params.
     *
     * @param string $type  The option type
     * @param string $value The option value
     *
     * @throws InvalidConfigurationException
     *
     * @return array The prefix and size
     */
    protected function getParams($type, $value)
    {
        if (false === strpos($value, '-')) {
            throw new InvalidConfigurationException(sprintf('The "%s" option must be configured with "{prefix}-{size}"', $type));
        }

        list($prefix, $size) = explode('-', $value);

        if (!\in_array($prefix, $this->validPrefix)) {
            throw new InvalidConfigurationException(sprintf('The "%s" prefix option does not exist. Known options are: "'.implode('", "', $this->validPrefix).'"', $type));
        }

        if (!(int) $size) {
            throw new InvalidConfigurationException(sprintf('The "%s" size option must be an integer', $type));
        }

        return [$prefix, $size];
    }
}
