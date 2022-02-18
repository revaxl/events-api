<?php

namespace App\Exceptions;

class NoAvailableTicketException extends \Exception
{
    protected $message = "Sold out!";
}
