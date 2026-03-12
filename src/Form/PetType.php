<?php

namespace App\Form;

use App\DTO\PetData;
use App\Support\PetOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'available' => 'available',
                    'pending' => 'pending',
                    'sold' => 'sold',
                ],
                'placeholder' => 'Wybierz status',
            ])
            ->add('selectedCategory', ChoiceType::class, [
                'label' => 'Kategoria',
                'required' => false,
                'placeholder' => 'Wybierz kategorię',
                'choices' => PetOptions::getCategoryChoices(),
            ])
            ->add('selectedTags', ChoiceType::class, [
                'label' => 'Tagi',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choices' => PetOptions::getTagChoices(),
            ])
            ->add('save', SubmitType::class, [
                'label' => $isEdit ? 'Zapisz zmiany' : 'Zapisz',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PetData::class,
            'is_edit' => false,
        ]);
    }
}