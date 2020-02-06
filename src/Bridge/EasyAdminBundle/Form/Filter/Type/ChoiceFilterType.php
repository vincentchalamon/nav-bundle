<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Form\Filter\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ComparisonFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class ChoiceFilterType extends AbstractType implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static function ($data) {
                return $data;
            },
            static function ($data) {
                if (ComparisonType::NEQ === $data['comparison']) {
                    $data['comparison'] = '<>';
                }

                return $data;
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'choice'],
            'value_type' => ChoiceType::class,
            'value_type_options' => [
                'multiple' => false,
                'attr' => [
                    'data-widget' => 'select2',
                ],
            ],
        ]);
        $resolver->setNormalizer('value_type_options', static function (Options $options, $value) {
            if (!isset($value['attr'])) {
                $value['attr']['data-widget'] = 'select2';
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ComparisonFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(RequestBuilderInterface $requestBuilder, FormInterface $form, array $metadata): void
    {
        $multiple = $form->get('value')->getConfig()->getOption('multiple');
        $values = $form->getData()['value'];
        $comparison = $form->getData()['comparison'];

        if (ComparisonType::EQ !== $comparison) {
            $values = array_map(function ($value) use ($comparison) {
                return "$comparison$value";
            }, $values);
        }

        if ($multiple) {
            $values = implode('|', $values);
        }

        $requestBuilder->andWhere($metadata['property'], $values);
    }
}
