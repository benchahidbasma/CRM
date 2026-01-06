<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('q', SearchType::class, ['required' => false, 'label' => 'Recherche'])
            ->add('city', TextType::class, ['required' => false, 'label' => 'Ville'])
            ->add('tag', TextType::class, ['required' => false, 'label' => 'Tag'])
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'choices' => ['Nom' => 'name', 'Ville' => 'city', 'Date' => 'created_at'],
                'label' => 'Tri',
            ])
            ->add('dir', ChoiceType::class, [
                'required' => false,
                'choices' => ['ASC' => 'ASC', 'DESC' => 'DESC'],
                'label' => 'Direction',
            ])
            ->add('search', SubmitType::class, ['label' => 'Rechercher']);
    }
}
