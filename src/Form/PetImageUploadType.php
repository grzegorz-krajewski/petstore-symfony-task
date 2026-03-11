<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PetImageUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $embedded = $options['embedded'];

        $builder
            ->add('additionalMetadata', TextType::class, [
                'label' => 'Dodatkowe metadane obrazów',
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'label' => 'Pliki obrazów',
                'required' => false,
                'multiple' => true,
            ]);

        if (!$embedded) {
            $builder->add('save', SubmitType::class, [
                'label' => 'Prześlij obrazy',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'embedded' => false,
        ]);
    }
}