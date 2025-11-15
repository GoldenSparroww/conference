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

    /**
     * Vrátí hodnotu enumu podle názvu case.
     * Použití: RolesID::getValue("author") -> "10"
     */
    public static function getValFromStr(string $name): ?string
    {
        $normalized = strtoupper($name); // převede na velká písmena
        foreach (self::cases() as $case) {
            if ($case->name === $normalized) {
                return $case->value;
            }
        }
        return null; // pokud nenajde
    }
}
