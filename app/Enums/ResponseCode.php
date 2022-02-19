<?php

namespace App\Enums;

class ResponseCode
{
    public const NO_MORE_TICKETS = 100;
    public const RESERVATIONS_BIGGER_THAN_ALLOWED = 101;
    public const INVALID_DATA = 102;
    public const NO_DATA_FOUND = 103;
    public const GENERAL_EXCEPTION = 104;
}
