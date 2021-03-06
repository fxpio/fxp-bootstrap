<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Bootstrap\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Collection Form Extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CollectionExtension extends AbstractTypeExtension
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['allow_add']) {
            $btnAdd = $options['btn_add'];

            if (\is_array($btnAdd)) {
                $btnAdd = $builder->create('add', ButtonType::class, $options['btn_add'])->getForm();
            }

            $builder->setAttribute('btn_add', $btnAdd);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getConfig()->hasAttribute('prototype')) {
            $view->vars['prototype_name'] = $options['prototype_name'];
        }

        if ($form->getConfig()->hasAttribute('btn_add')) {
            $add = $form->getConfig()->getAttribute('btn_add');
            $view->vars['btn_add'] = $add->createView($view);
            $view->vars['btn_add']->vars['attr']['data-target'] = $view->vars['id'];
            $view->vars['btn_add']->vars['disabled'] = $view->vars['disabled'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'btn_add' => [],
            'btn_delete' => [],
        ]);

        $resolver->addAllowedTypes('btn_add', ['array', 'Symfony\Component\Form\Form']);
        $resolver->addAllowedTypes('btn_delete', ['array', 'Symfony\Component\Form\Form']);

        $btnAddNormalizer = function (Options $options, $value) {
            if (\is_array($value)) {
                $value = array_merge(
                    [
                        'label' => '',
                        'glyphicon' => 'plus',
                        'size' => 'xs',
                        'style' => 'default',
                        'attr' => ['class' => 'btn-add'],
                    ],
                    $value
                );
            }

            return $value;
        };

        $optionsNormalizer = function (Options $options, $value) {
            $value['label_attr'] = ['class' => 'sr-only'];

            // btn delete
            if ($options['allow_delete'] && $options['prototype']) {
                $value['append'] = $options['btn_delete'];

                if (\is_array($value['append'])) {
                    $value['append'] = array_merge(
                        [
                            'label' => '',
                            'glyphicon' => 'remove',
                            'style' => 'danger',
                            'disabled' => $options['disabled'],
                            'attr' => [
                                'class' => 'btn-remove',
                            ],
                        ],
                        $value['append']
                    );

                    $value['append'] = $this->factory->createNamed('delete', ButtonType::class, null, $value['append']);
                }

                $value['row_attr'] = ['class' => 'form-collection-row'];
            }

            return $value;
        };

        $resolver->setNormalizer('btn_add', $btnAddNormalizer);
        $resolver->setNormalizer('entry_options', $optionsNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [CollectionType::class];
    }
}
