<?php

class HomeController extends Controller
{
    public function index(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->redirect('login');
    }
}
