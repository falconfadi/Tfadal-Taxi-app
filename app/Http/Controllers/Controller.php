<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Traits\HasRoles;

//use Auth;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,HasRoles;

    //protected $isAdmin = Auth::guard('admin')->user()?->role('Super-Admin');
    protected $id;
    public function __construct()
    {
        App::setLocale('ar');
        session()->put('locale', 'ar');

        $x = new Setting();
        $setting = $x->getSetting();
        View::share('setting',$setting);

        //get the role of user is he is super admin or not
        $this->middleware(function ($request, $next) {
            $xx = (Auth::guard('admin')->user())?Auth::guard('admin')->user()->hasRole('Super-Admin'):false;
            View::share('isAdmin', $xx);
            return $next($request);
        });
    }

    public function getUserId()
    {
        $x = Auth::guard('admin')->id();
        return $x;
    }
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $e='';
        foreach ($error as $item=>$key){
            //$e = $key[0];
            $e = $item;
            break;
        }
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}
