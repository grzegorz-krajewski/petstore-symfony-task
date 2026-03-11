<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PetImageUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('additionalMetadata', TextType::class, [
                'label' => 'Dodatkowe metadane',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Obraz',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Prześlij obraz',
            ]);
    }
}