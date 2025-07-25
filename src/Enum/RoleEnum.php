<?php

namespace App\Enum;

enum RoleEnum: string
{
    case AGENT = 'Agent';
    case TECHNICIEN = 'Technicien';
    case RESPONSABLE = 'Responsable';
}
