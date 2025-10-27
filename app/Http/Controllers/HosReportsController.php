<?php

namespace App\Http\Controllers;

class HosReportsController extends Controller
{
    public function __invoke()
    {
        return view('hos-reports.index');
    }
}
