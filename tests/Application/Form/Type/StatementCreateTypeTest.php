<?php

namespace App\Tests\Application\Form\Type;

use App\Entity\Statement;
use App\Entity\User;
use App\Enum\Custom\StatementTypes;
use App\Form\Extension\DefaultFormTypeExtension;
use App\Form\Type\StatementCreateType;
use App\Tests\Service\AppConstraintValidatorFactory;
use DateTime;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class StatementCreateTypeTest extends TypeTestCase
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
     * @return FormTypeExtensionInterface[]
     */
    protected function getTypeExtensions(): array
    {
        return [
            new DefaultFormTypeExtension
        ];
    }

    /**
     * Тест формы создания заявления
     */
    public function testSubmitValidData(): void
    {
        // Для подстановки значений по умолчанию в форму
        $user = new User;
        $user->setFullName('user name');
        $user->setPhone('user phone');

        $formData = [
            'fullName' => 'Test Igor Igorevich',
            'email' => 'client@test.ru',
            'comment' => 'комментарий комментарий комментарий',
            'date' => '1998-11-25',
            'phone' => '7123456786',
            'number' => 'ad32ref',
            'typeId' => StatementTypes::FIRST['id'],
            'creator' => $user
        ];

        $model = new Statement;
        $form = $this->factory->create(StatementCreateType::class, $model, compact('user'));
        
        $expected = new Statement;
        $expected->setFullName($formData['fullName']);
        $expected->setTypeId($formData['typeId']);
        $expected->setComment($formData['comment']);
        $expected->setNumber($formData['number']);
        $expected->setDate(new DateTime($formData['date']));
        $expected->setPhone($formData['phone']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected, $model);
    }
}