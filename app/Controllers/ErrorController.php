<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ErrorController extends Controller
{
    public function index()
    {
        return view('errors/custom_error');
    }
}
