<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    /**
     * @param $label
     * @param $placeholder
     * @param array $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = [])
    {
        return array_merge([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, $this->getConfiguration("Pseudo", "Votre pseudo..."))

            ->add('password', PasswordType::class, $this->getConfiguration("Mot de passe",
                "Votre mot de passe..."))

            ->add('confirmationPassword', PasswordType::class,$this->getConfiguration
            ("Confirmation mot de passe","Veuillez confirmer votre mot de passe..."))

            ->add('sexe', ChoiceType::class, array(
                'choices' => array(

                    'Feminin' => 'Feminin',
                    'Masculin' => 'Masculin'
                )
            ))
            ->add('email', EmailType::class,$this->getConfiguration("Email","Votre email..."))

            ->add('imageUser',UrlType::class,$this->getConfiguration
            ("Photo de profil","Url de votre avatar (optional)...",[

                'required'=>false
            ]))

            ->add('age',IntegerType::class,$this->getConfiguration
            ("Age","Votre âge (optional)...",[

                'required'=>false
            ]))

            ->add('city', TextType::class,$this->getConfiguration("Ville","Votre ville..."));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
