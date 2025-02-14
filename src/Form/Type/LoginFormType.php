<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'label' => 'Эл. почта',
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'Эл. почта',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите эл. почту',
                    ]),
                    new Length([
                        'maxMessage' => 'Максимум {{ limit }} символов',
                        'max' => 180,
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Пароль',
                    'maxlength' => 4096
                ],
                'row_attr' => [
                    'class' => 'form-floating col-sm-3',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите пароль',
                    ]),
                ],
            ])
        ;
    }
}
