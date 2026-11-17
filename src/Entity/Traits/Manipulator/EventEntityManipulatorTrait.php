<?php

namespace Wexample\SymfonyEvent\Entity\Traits\Manipulator;

use Wexample\SymfonyEvent\Entity\Event;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait EventEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return Event::class;
    }
}
