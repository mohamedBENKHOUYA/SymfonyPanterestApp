<?php

namespace App\Form;

use App\Entity\Pin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FormPinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        isset($options['data']) can be used
        $isEdit = $options['method'] === 'PUT';
        $imageFileConstraints = ['maxSize' => '8M'];
        if ($isEdit) {
            $imageFileConstraints['maxSizeMessage'] = "can't update, this size not allowed";
        }
        else {
            $imageFileConstraints['maxSizeMessage'] = "can't create, size not allowed";
        }

        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'title'],
                'required' => false,
            ])
            ->add('description', null, [
                'attr' => ['class' => 'description'],
                'required' => false,

            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Delete image ? ',
                'download_label' => 'Download',
                'download_uri' => true,
                'image_uri' => true,
                'imagine_pattern' => 'squared_thumbnail_small',
                'asset_helper' => true,
                'attr' => ['class' => 'chooseFile'],
                'label' => 'Image(JPG or PNG file)',
                'constraints' => [
                    new File($imageFileConstraints)
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
//        $pin = $form->getData(); ****
//            validation form
        //deviner textareatype
            $resolver->setDefaults([
                'data_class' => Pin::class,
            ]);

    }


}
