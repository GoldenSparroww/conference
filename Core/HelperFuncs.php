<?php

namespace App\Core;

class HelperFuncs {
    public static function path_join(string ...$segments): string
    {
        // Vyčistíme každý segment od počátečních/koncových lomítek
        $cleaned_segments = array_map(function($segment) {
            return trim($segment, DIRECTORY_SEPARATOR);
        }, $segments);

        // Odstraníme prázdné segmenty (pokud byly zadány) a spojíme je
        return implode(DIRECTORY_SEPARATOR, array_filter($cleaned_segments));
    }
}