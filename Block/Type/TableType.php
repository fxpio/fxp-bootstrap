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
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockRendererInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use Fxp\Component\Block\Util\BlockUtil;
use Fxp\Component\Bootstrap\Block\DataSource\DataSource;
use Fxp\Component\Bootstrap\Block\DataSource\DataSourceInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Table Block Type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TableType extends AbstractType
{
    /**
     * @var BlockRendererInterface
     */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param BlockRendererInterface $renderer
     */
    public function __construct(BlockRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        if (\is_array($builder->getData())) {
            $source = new DataSource($options['row_id']);
            $source->setPageSizeMax($options['page_size_max']);
            $source->setPageSize($options['page_size']);
            $source->setRows($builder->getData());
            $source->setLocale($options['locale']);
            $source->setSortColumns($options['sort_columns']);
            $source->setParameters($options['data_parameters']);
            $source->setPageNumber($options['page_number']);

            $builder->setData($source);
            $builder->setDataClass(\get_class($source));
        }

        $builder->add('_header', TableHeaderType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function finishBlock(BlockBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        if ($data instanceof DataSourceInterface) {
            foreach ($options['data_transformers'] as $transformer) {
                $data->addDataTransformer($transformer);
            }

            $data->setRenderer($this->renderer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        if (BlockUtil::isBlockType($child, TableHeaderType::class)) {
            if ($block->has('_header')) {
                $block->remove('_header');
            }
        } elseif ($this->isColumn($child->getConfig()->getType())) {
            $block->getData()->addColumn($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        if ($this->isColumn($child->getConfig()->getType())) {
            $block->getData()->removeColumn($child->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $block->getData()->setTableView($view);

        $view->vars = array_replace($view->vars, [
            'striped' => $options['striped'],
            'bordered' => $options['bordered'],
            'condensed' => $options['condensed'],
            'responsive' => $options['responsive'],
            'hover_rows' => $options['hover_rows'],
            'empty_type' => $options['empty_type'],
            'empty_options' => $options['empty_options'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $columns = [];

        foreach ($view->children as $name => $child) {
            if (\in_array('table_caption', $child->vars['block_prefixes'])) {
                $view->vars['caption'] = $child;
                unset($view->children[$name]);
            } elseif (\in_array('table_header', $child->vars['block_prefixes'])) {
                $view->vars['header'] = $child;
                unset($view->children[$name]);
            } elseif (\in_array('table_footer', $child->vars['block_prefixes'])) {
                $view->vars['footer'] = $child;
                unset($view->children[$name]);
            } elseif (\in_array('table_column', $child->vars['block_prefixes'])) {
                $columns[] = $child;
                unset($view->children[$name]);
            }
        }

        $view->vars['header']->vars['header_columns'] = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'striped' => false,
            'bordered' => false,
            'condensed' => false,
            'responsive' => false,
            'hover_rows' => false,
            'data' => [],
            'data_transformers' => [],
            'locale' => \Locale::getDefault(),
            'page_size' => 0,
            'page_size_max' => 2000,
            'page_start' => 1,
            'page_number' => 1,
            'sort_columns' => [],
            'data_parameters' => [],
            'row_id' => 'id',
            'empty_type' => function (Options $options, $value) {
                if (null === $value && isset($options['empty_options']['data'])) {
                    $value = TextType::class;
                }

                return $value;
            },
            'empty_options' => [],
        ]);

        $resolver->setAllowedTypes('striped', 'bool');
        $resolver->setAllowedTypes('bordered', 'bool');
        $resolver->setAllowedTypes('condensed', 'bool');
        $resolver->setAllowedTypes('responsive', 'bool');
        $resolver->setAllowedTypes('hover_rows', 'bool');
        $resolver->setAllowedTypes('data', ['array', DataSourceInterface::class]);
        $resolver->setAllowedTypes('data_transformers', 'array');
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('page_size', 'int');
        $resolver->setAllowedTypes('page_start', 'int');
        $resolver->setAllowedTypes('page_number', 'int');
        $resolver->setAllowedTypes('sort_columns', 'array');
        $resolver->setAllowedTypes('data_parameters', 'array');
        $resolver->setAllowedTypes('row_id', 'string');
        $resolver->setAllowedTypes('empty_type', ['null', 'string']);
        $resolver->setAllowedTypes('empty_options', 'array');

        $resolver->setNormalizer('empty_options', function (Options $options, $value) {
            if (null !== $options['empty_message'] && !isset($value['data'])) {
                $value['data'] = $options['empty_message'];
            }

            if (!array_key_exists('wrapped', $value)) {
                $value['wrapped'] = false;
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'table';
    }

    /**
     * Check if the child is a column.
     *
     * @param ResolvedBlockTypeInterface $type
     *
     * @return bool
     */
    protected function isColumn(ResolvedBlockTypeInterface $type)
    {
        if ('table_column' === $type->getBlockPrefix()) {
            return true;
        }

        if (null !== $type->getParent()) {
            return $this->isColumn($type->getParent());
        }

        return false;
    }
}
