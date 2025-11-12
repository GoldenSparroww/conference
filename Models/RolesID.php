<?php

namespace App\Models;

enum RolesID: string {
    case AUTHOR = '10';
    case REVIEWER = '20';
    case ADMIN = '100';
    case SUPERADMIN = '200';
}
