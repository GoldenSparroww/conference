<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->view('Home.twig', ['title' => 'Vítej na webu']);
    }
}
