<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    protected function authorizePermission(string $permission): void
    {
        Gate::authorize($permission);
    }
}
