<?php

namespace App\Enums;

enum ErrorEnvironment: string
{
    case DEV = 'dev';
    case PROD = 'prod';
    case LOCAL = 'local';

}
