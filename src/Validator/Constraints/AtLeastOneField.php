<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AtLeastOneField extends Constraint
{
    public string $path;

    public function __construct(?array $options = null)
    {
        parent::__construct($options);

        if (count($options) > 0) {
            $this->path = $options['path'];
        }
    }
}
