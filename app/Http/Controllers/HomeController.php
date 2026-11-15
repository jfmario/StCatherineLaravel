<?php

namespace App\Http\Controllers;

use App\Support\LiturgicalCalendar;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(LiturgicalCalendar $liturgicalCalendar): View
    {
        return view('welcome', [
            'liturgicalColor' => $liturgicalCalendar->colorAt(now()),
        ]);
    }
}
