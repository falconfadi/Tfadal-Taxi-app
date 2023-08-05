<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\App;

class AdminController extends Controller
{
    public function __construct()
    {
         //$this->middleware('auth:admin');

    }
    public function index()
    {
        return view('fad');
    }
}
