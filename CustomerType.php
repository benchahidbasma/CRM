<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerId', TextType::class, [
                'label' => 'ID Client',
            ])
            ->add('customerName', TextType::class, [
                'label' => 'Nom du Client',
            ])
            ->add('segment', TextType::class, [
                'required' => false,
            ])
            ->add('country', TextType::class, [
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'required' => false,
            ])
            ->add('state', TextType::class, [
                'required' => false,
            ])
            ->add('region', TextType::class, [
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
