<?php

namespace App\Form;

use App\Entity\Facture;
use App\Entity\Customer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', MoneyType::class, [
                'label' => 'Montant',
                'currency' => 'MAD',
                'required' => true,
            ])
            ->add('sentAt', DateTimeType::class, [
                'label' => 'Date d\'envoi',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Payée' => 'Payée',
                    'En attente' => 'En attente',
                    'Annulée' => 'Annulée',
                ],
                'required' => true,
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'customerName',
                'label' => 'Client',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
