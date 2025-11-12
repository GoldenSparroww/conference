<?php

namespace App\Models;

/**
 * This is derived from database scheme
 */
enum RolesID: string {
    case AUTHOR = '10';
    case REVIEWER = '20';
    case ADMIN = '100';
    case SUPERADMIN = '200';
}
