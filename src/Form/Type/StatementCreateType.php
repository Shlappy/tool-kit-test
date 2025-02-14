<?php

namespace App\Form\Type;

use App\Entity\Statement;
use App\Entity\User;
use App\Enum\Custom\StatementTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StatementCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('number', null, [
                'label' => 'Номер',
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'Эл. почта',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите номер',
                    ]),
                ],
            ])
            ->add('fullName', null, [
                'label' => 'ФИО',
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'default' => $user->getFullName(),
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
            ->add('date', DateType::class, [
                'label' => 'Дата',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                ],
                'attr' => [
                    'placeholder' => 'Дата',
                ],
            ])
            ->add('phone', null, [
                'label' => 'Телефон',
                'required' => false,
                'default' => $user->getPhone(),
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
            ->add('comment', TextareaType::class, [
                'label' => 'Комментарий',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-floating mb-3',
                ],
                'attr' => [
                    'placeholder' => 'Комментарий',
                ],
            ])
            ->add('typeId', ChoiceType::class, [
                'label' => 'Тип',
                'required' => false,
                'choices'  => [
                    StatementTypes::FIRST['title'] => StatementTypes::FIRST['id'],
                    StatementTypes::REPEATED['title'] => StatementTypes::REPEATED['id'],
                ],
                'row_attr' => [
                    'class' => 'form-floating mb-3 col-sm-3',
                    'rows' => 4
                ],
                'attr' => [
                    'placeholder' => 'Тип',
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Файл',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'col-sm-6',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '32m'
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Statement::class,
            'user' => null
        ]);
    }
}
