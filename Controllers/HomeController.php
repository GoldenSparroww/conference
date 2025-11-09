<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index(): void {
        echo $this->view->render('Home.twig', [
            'title' => 'Vítej na webu',
        ]);
    }
}
