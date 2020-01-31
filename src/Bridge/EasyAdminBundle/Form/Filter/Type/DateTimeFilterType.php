<?php

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Form\Filter\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ComparisonFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\FormTypeHelper;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class DateTimeFilterType extends AbstractType implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('value2', FormTypeHelper::getTypeClass($options['value_type']), $options['value_type_options'] + [
                'label' => false,
            ]
        );

        $builder->addModelTransformer(new CallbackTransformer(
            static function ($data) {
                return $data;
            },
            static function ($data) use ($options) {
                if (ComparisonType::NEQ === $data['comparison']) {
                    $data['comparison'] = '<>';
                }

                if (ComparisonType::BETWEEN === $data['comparison']) {
                    if (null === $data['value'] || '' === $data['value'] || null === $data['value2'] || '' === $data['value2']) {
                        throw new TransformationFailedException('Two values must be provided when "BETWEEN" comparison is selected.');
                    }

                    // make sure end datetime is greater than start datetime
                    if ($data['value'] > $data['value2']) {
                        [$data['value'], $data['value2']] = [$data['value2'], $data['value']];
                    }
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
            'comparison_type_options' => ['type' => 'datetime'],
            'value_type' => DateType::class,
            'value_type_options' => [
                'widget' => 'single_text',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'easyadmin_datetime_filter';
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
    public function filter(RequestBuilderInterface $requestBuilder, FormInterface $form, array $metadata)
    {
        $property = $metadata['property'];
        $data = $form->getData();

        switch ($data['comparison']) {
            case ComparisonType::BETWEEN:
                $requestBuilder->andWhere($property, '>='.$data['value'].'&<='.$data['value2']);
                break;
            case ComparisonType::EQ:
                $requestBuilder->andWhere($property, $data['value']);
                break;
            default:
                $requestBuilder->andWhere($property, $data['comparison'].$data['value']);
                break;
        }
    }
}
