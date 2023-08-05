<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Balance;
use App\Models\Setting;
use App\Models\Sum_money;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SumMoneyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->middleware('auth:admin');

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = __('menu.SumOfMoney');
        $requests = Sum_money::with('driver')->get();

        $drivers = User::where('is_driver',1)->whereNotNull('fcm_token')->get();

        $S = new  Setting();
        $maxBalance = $S->getSetting()->max_amount_to_stop_driver;

        return view('admin.money.index',compact('requests','title','drivers','maxBalance'));
    }


    public function sumMoneyAcheivedPerMonth(Request $request){

        $title = __('menu.SumOfMoney');
        $X = new Sum_money();
        $result = $X->sumMoneyAcheivedPerMonth($request->driver_id, $request->month, $request->year);
        $month = date("F", strtotime($request->month));
        $date = $request->year."-".$month;
        $driver_name = User::find($request->driver_id)->name;
        return view('admin.money.month',compact('result','driver_name','date','title'));
    }

    public function sumMoneyAcheivedPerDay($driver_id,$price )
    {
        $today = Carbon::now();
        //if driver have record today
        $x = Sum_money::where('driver_id',$driver_id)->where('work_day', $today->toDateString())->first();
        if($x){
            $x->amount += $price;
            //delete the cost of trip from the balance of driver
            $x->balance += $price;
            $x->save();
            return true;
        }
        else{
            //if driver have a record before
            $x = Sum_money::where('driver_id',$driver_id)->latest()->first();
            $S = new  Setting();
            $maxBalance = $S->getSetting()->max_amount_to_stop_driver;
            if($x){
                $y = new Sum_money();
                $y->amount =  $price;
                $y->driver_id =  $driver_id;
                //get balance from last work day
                $y->balance = $x->balance + $price;
                $y->work_day = $today->toDateString();
                $y->save();
            }
            else{
                //add new record for this driver
                $y = new Sum_money();
                $y->amount =  $price;
                $y->driver_id =  $driver_id;
                $y->balance =  $price;
                $y->work_day = $today->toDateString();
                $y->save();
            }
            return true;
        }
    }

    public function renew_balance(Request $request){

        $today = Carbon::now();
        $x = Sum_money::where('driver_id',$request->driver_id)->latest()->first();

        $b = new Balance();
        $b->driver_id = $request->driver_id;
        $b->renew_amount = $request->balance;
        $b->ishaar = $request->ishaar;
        $b->image = '';
        if($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = 'ishaar/' . md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
            if ($file->move('storage/ishaar/', $file_name)) {
                $b->image = $file_name;
            }
        }
        $b->save();


        if($x){
            $x->balance -= $request->balance;
            $x->save();
        }
        else{
            //add new record for this driver
            $y = new Sum_money();
            $y->amount =  0;
            $y->driver_id =  $request->driver_id;
            $y->balance = $request->balance*(-1);
            $y->work_day = $today->toDateString();
            $y->save();
        }
        Session::flash('alert_success','تم تجديد الرصيد');
        return redirect('admin/drivers/money');
    }

    public function test(){
//        $user_id = 25 ;
//        $this->sumMoneyAcheivedPerDay($user_id,25);
        $f = new User();
        $r = $f->getAvailableDrivers1();
        var_dump($r);

    }
}
