<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function setLocale($locale)
    {
        if (in_array($locale, ['en', 'pl'])) {
            Session::put('locale', $locale);
        }
        return redirect()->back();
    }
}