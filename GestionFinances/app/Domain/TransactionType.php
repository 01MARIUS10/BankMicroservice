<?php

namespace App\Domain;

enum TransactionType: string
{
    case INCOME = 'income';
    case OUTCOME = 'outcome';
}
