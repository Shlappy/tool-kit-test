<?php

namespace App\Tests\Application\Form\Type;

use App\Entity\User;
use App\Enum\Roles;
use App\Form\Type\LoginFormType;
use App\Tests\Service\AppConstraintValidatorFactory;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class LoginFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $factory = new AppConstraintValidatorFactory();
        $factory->addValidator(
            'doctrine.orm.validator.unique',
            $this->createMock(UniqueEntityValidator::class)
        );

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($factory)
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    /**
     * Тест формы входа в профиль
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'client@test.ru',
        ];

        $model = new User;
        $form = $this->factory->create(LoginFormType::class, $model);

        $expected = new User;
        $expected->setEmail($formData['email']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected, $model);
    }
}