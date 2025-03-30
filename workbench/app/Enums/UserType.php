<?php

namespace Workbench\App\Enums;

enum UserType: string
{
    case END_USER = 'End User';
    case AGENT = 'Agent';
    case ADMIN = 'Admin';
}
