<?php
namespace App\Core;

abstract class Controller {
    protected ViewWrapper $view;

    public function __construct() {
        $this->view = new ViewWrapper();
    }
}
