<?php

namespace App\Exceptions;

class BiggerThanTicketLimitException extends \Exception
{
    protected $message = "You cannot exceed the amount of tickets available!";
}
