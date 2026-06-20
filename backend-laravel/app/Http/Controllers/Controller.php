<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

abstract class Controller
{
    protected function redirectToRouteWithStatus(string $routeName, string $message): Response
    {
        session()->flash('status', $message);

        return response('', 302)
            ->header('Location', route($routeName, [], false));
    }
}
