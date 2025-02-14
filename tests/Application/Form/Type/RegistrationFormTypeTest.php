<?php

namespace App\Tests\Application\Form\Type;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Tests\Service\AppConstraintValidatorFactory;
use DateTime;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class RegistrationFormTypeTest extends TypeTestCase
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
     * Тест формы регистрации (заполнения анкеты)
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'fullName' => 'Test Igor Igorevich',
            'email' => 'client@test.ru',
            'address' => 'г. Москва, ул. Ленина, д. 37, кв. 32',
            'birthDate' => '1998-11-25',
            'phone' => '7123456786',
        ];

        $model = new User;
        $form = $this->factory->create(RegistrationFormType::class, $model);

        $expected = new User;
        $expected->setFullName($formData['fullName']);
        $expected->setEmail($formData['email']);
        $expected->setAddress($formData['address']);
        $expected->setBirthDate(new DateTime($formData['birthDate']));
        $expected->setPhone($formData['phone']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected, $model);
    }
}