<?php

namespace App\Form;

use App\DTO\PetData;
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
            ->add('id', IntegerType::class, [
                'label' => 'ID',
                'disabled' => $isEdit,
            ])
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
            ->add('categoryId', IntegerType::class, [
                'label' => 'ID kategorii',
                'required' => false,
            ])
            ->add('categoryName', TextType::class, [
                'label' => 'Nazwa kategorii',
                'required' => false,
            ])
            ->add('tagsInput', TextType::class, [
                'label' => 'Tagi',
                'required' => false,
                'help' => 'Wpisz tagi oddzielone przecinkami.',
            ])
            ->add('imageUpload', PetImageUploadType::class, [
                'label' => false,
                'mapped' => false,
                'embedded' => true,
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