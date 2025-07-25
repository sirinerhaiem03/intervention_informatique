<?php

namespace App\Enum;

enum StatutEnum: string
{
    case EN_ATTENTE = 'EN ATTENTE';
    case EN_COURS = 'EN COURS';
    case TERMINEE = 'TERMINEE';
}
