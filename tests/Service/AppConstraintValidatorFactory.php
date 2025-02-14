<?php

namespace App\Tests\Service;

use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class AppConstraintValidatorFactory extends ConstraintValidatorFactory
{
    public function addValidator(string $className, ConstraintValidatorInterface $validator): void
    {
        $this->validators[$className] = $validator;
    }
}