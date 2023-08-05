<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;

class VerificationCodeController extends Controller
{
    public function index()
    {
        $title = __('menus.verification_codes');
        $codes = User::orderBy('id', 'desc')->take(50)->get();

        return view('admin.verification_code.index',compact('codes','title'));
    }
}
