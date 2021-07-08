<?php

namespace App\Form;

use App\Entity\Pin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FormPinCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Delete image ? ',
                'download_label' => 'Download',
                'download_uri' => true,
                'image_uri' => true,
                'imagine_pattern' => '',
                'asset_helper' => true,
                'attr' => ['class' => 'chooseFile'],
                'label' => 'Image(JPG or PNG file',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pin::class,
            'validation_groups' => ['Default', 'pin_create_validation']
        ]);
    }
}
