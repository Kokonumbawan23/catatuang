<?php

namespace App\Enums;

enum BalanceAlertLevel: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
}
