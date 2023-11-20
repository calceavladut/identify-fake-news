<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AtLeastOneFieldValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (property_exists($value, 'getUrl')) {
            if (!$value->getUrl() && !$value->getText()) {
                $this->context
                    ->buildViolation('Please give us something to work with...')
                    ->atPath($constraint->path)
                    ->addViolation();
            }
        }
    }
}
