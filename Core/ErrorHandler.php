<?php

namespace App\Core;

use Throwable;
use ErrorException;

class ErrorHandler
{
    public function register(): void
    {
        // OD VERZE PHP 8 SE DÍKY THROWABLE HODNĚ VĚCÍ ZJEDNODUŠILO
        // (např. register_shutdown_function([$this, 'handleShutdown']) už není třeba)

        // Zpracování běžných PHP chyb
        set_error_handler([$this, 'handleError']);
        // Zpracování výjimek (throwables)
        set_exception_handler([$this, 'handleException']);
    }

    //Převod běžných chyb a varování na výjimky (např. Undefined variable $as)
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new ErrorException($errstr, 500, $errno, $errfile, $errline);
    }

    public function handleException(Throwable $exception): void
    {
        $code = $exception->getCode();
        // Pojistka, kdyby přišel nevalidní kód (žádný/0, 999, -1 atd.)
        // HTTP status kódy v rozsahu 400–599 jsou vyhrazeny pro chyby
        $status = ($code >= 400 && $code < 600) ? $code : 500;
        http_response_code($status);

        $view = new ViewWrapper();
        echo $view->render('Error.twig', [
            'error_code' => $status,
            'error_message' => $this->getErrorMessage($exception),
            //todo, dat pryc
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
        ]);
    }

    public function getErrorMessage(Throwable $exception): string
    {
        //TODO, implementovat, asi nechci uplne videt co presne je spatne pokud chyba bude vyvolana zvenku necekane (nebezpeci)
        return $exception->getMessage();
    }
}
