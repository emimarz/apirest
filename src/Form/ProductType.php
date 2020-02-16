<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add(
                'currency',
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        Product::getCurrenciesTypes(),
                        Product::getCurrenciesTypes()
                    ),
                    'multiple' => false,
                    'constraints' => [
                        new NotBlank()
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_extra_fields' => true
        ]);
    }
}
