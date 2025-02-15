<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
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
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Минимум {{ limit }} символов',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('fullName', null, [
                'label' => 'ФИО',
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'ФИО',
                    'maxlength' => 200
                ],
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимум {{ limit }} символов',
                        'max' => 200,
                    ]),
                ],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Дата рождения',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'Дата рождения',
                ],
            ])
            ->add('address', null, [
                'label' => 'Адрес регистрации',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-floating mb-3',
                ],
                'attr' => [
                    'placeholder' => 'Адрес регистрации',
                    'maxlength' => 500
                ],
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимум {{ limit }} символов',
                        'max' => 500,
                    ]),
                ],
            ])
            ->add('phone', null, [
                'label' => 'Телефон',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'Телефон',
                    'maxlength' => 40
                ],
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимум {{ limit }} символов',
                        'max' => 40,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'row_attr' => [
                    'class' => 'mb-4',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Обязательное поле'
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
