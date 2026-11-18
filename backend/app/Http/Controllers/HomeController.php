<?php

namespace App\Http\Controllers;

use App\Models\SuiteModule;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Corporate one-page site. The Suite modules come from the database.
     */
    public function __invoke(): View
    {
        return view('home', [
            'modules' => SuiteModule::ordered()->get(),
        ]);
    }
}
