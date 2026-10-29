<?php

namespace App\Controllers;

class Migration extends BaseController
{
    public function index(): string
    {
        return view('migration_status', [
            'ci4Version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
