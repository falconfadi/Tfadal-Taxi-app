<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Car_model;
use App\Models\Car_type;
use App\Models\Color;
use App\Models\Coordinate;
use App\Models\Driver;
use App\Models\FreezeReason;
use App\Models\Notification;
use App\Models\Offers;
use App\Models\Policy;
use App\Models\Setting;
use App\Models\Sum_money;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class DriverController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = __('menus.Drivers');
        $D = new User();
        $drivers = $D->getAllDrivers();//User::where('is_driver',1)->get();
        $det = array();

        $S = new  Setting();
        $maxBalance = $S->getSetting()->max_amount_to_stop_driver;
        //$balance = Sum_money::where()
        $x = new Sum_money();
        $balances = $x->driversBalancesToday();

        $notAvailableDriversIds = $D->getDriversIdswithActiveTrip();
        return view('admin.drivers.index',compact('drivers','det', 'balances','notAvailableDriversIds'));
    }

    public function verify($id)
    {
        $driver = Driver::where('id',$id)->first();
        if($driver)
        {
            $driver->verified = 1;
            if( $driver->save())
            {
                session()->flash('alert-success', __('message.driver_verified'));
                return redirect('/admin/drivers');
            }
        }
    }

    public function freeze(Request $request)
    {
        //var_dump($request->reason);var_dump($request->driver_id);exit();
        $driver = Driver::findOrFail($request->driver_id);
        if($driver)
        {
            $data = array('freeze' => 1);
            $driver->update($data);

            $reason = FreezeReason::create(['driver_id'=>$driver->user_id, 'reason'=>$request->reason,'is_freeze'=>1]);

            session()->flash('alert-success', __('message.driver_freezed'));
            return redirect('/admin/drivers');

        }
        else{
            session()->flash('alert-success', 'No Data');
            return redirect('/admin/drivers');
        }
    }

    public function unfreeze($id)
    {
        $driver = Driver::findOrFail($id);
        if($driver)
        {
            $data = array('freeze' => 0);
            $driver->update($data);

            $reason = FreezeReason::create(['driver_id'=>$driver->user_id, 'reason'=>'','is_freeze'=>0]);

            session()->flash('alert-success', 'تم إلغاء تجميد السائق');
            return redirect('/admin/drivers');

        }
        else{
            session()->flash('alert-success', 'No Data');
            return redirect('/admin/drivers');
        }
    }

    public function unVerify($id)
    {
        $driver = Driver::findOrFail($id);
        if($driver)
        {
            $data = array('verified' => 0);
            $driver->update($data);

            //$reason = FreezeReason::create(['driver_id'=>$driver->user_id, 'reason'=>'','is_freeze'=>0]);

            session()->flash('alert-success', 'تم إلغاء تثبيت الكابتن');
            return redirect('/admin/drivers');

        }
        else{
            session()->flash('alert-success', 'No Data');
            return redirect('/admin/drivers');
        }
    }

    public function view($id)
    {
        //echo $id;
        $driver = Driver::with('driver_as_user')->findOrFail($id);
        if($driver)
        {
            //get money acheived per this month
            $X = new Sum_money();
            $year = date('Y');
            $month = date('m');
            $result = $X->sumMoneyAcheivedPerMonth($driver->user_id, $month, $year);
            //var_dump($result);exit();
            $sum_amount = (!empty($result))?$result[0]->sum_amount:0;

            $balance = ($X->driverBalance($driver->user_id))?$X->driverBalance($driver->user_id)->balance:0;
            $car = Car::where('driver_id',$driver->user_id)->first();

            $lastTrips = Trip::where('driver_id',$driver->user_id)->orderBy('id','desc')->limit(1)->pluck('start_date')->toArray();

            return view('admin.drivers.view',compact('driver','car','sum_amount','lastTrips','balance'));
        }
    }

    public function edit($id)
    {
        $title = __('page.Edit_Driver');
        $driver = Driver::with('driver_as_user')->findOrFail($id);
        //var_dump($driver);exit();
        $car = Car::where('driver_id',$driver->user_id)->first();
        $car_model = Car_model::find($car->car_model);
        $car_types = Car_type::all();
        $colors = Color::all();
        $brands = Brand::all();
        return view('admin.drivers.edit',compact('driver','title','car_types','brands','car','car_model','colors'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //echo url()->previous();exit();
        $messages = [
            'image.mimes' => trans('validation.mimes'),
            'image.max.file' => trans('validation.max.file'),
        ];

        $driver = Driver::find($request->driver_id);

            $validator = Validator::make($request->all(), [
                'image' => 'mimes:jpeg,png,jpg,gif,svg|max:2048'
            ],$messages);

            if ($validator->fails()) {
                return redirect('admin/drivers/edit/'.$request->driver_id)
                    ->withErrors($validator)
                    ->withInput();
            }
            if($request->hasFile('personal_id_image')){
                $file = $request->file('personal_id_image');
                $file_name = 'drivers/id_lisence/'.md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
                if ($file->move('storage/drivers/id_lisence', $file_name)) {
                    $driver->personal_id_image = $file_name;
                }
            }
            if($request->hasFile('back_personal_id_image')){
                $file = $request->file('back_personal_id_image');
                $file_name = 'drivers/id_lisence/'.md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
                if ($file->move('storage/drivers/id_lisence', $file_name)) {
                    $driver->back_personal_id_image = $file_name;
                }
            }
            $driver->last_name = $request->input('last_name');
            $driver->father_name = $request->input('father_name');
            $driver->birthdate = $request->input('birthdate');
            $driver->marital_status = $request->input('marital_status');

            if ( $driver->update()){
                $user = User::find($driver->user_id);
                if($request->hasFile('image')) {
                    $file = $request->file('image');
                    $file_name = 'drivers/'.md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
                    if ($file->move('storage/drivers/', $file_name)) {
                        $user->image = $file_name;
                    }
                }
                $user->name = $request->input('name');
                $user->phone = $request->input('phone');
                $user->gender = $request->input('gender');
                $user->address = $request->input('address');
                $user->update();

                $car = Car::where('driver_id',$driver->user_id)->first();
                if($request->hasFile('car_image')){
                    $file = $request->file('car_image');
                    $file_name = 'cars/'.md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
                    if ($file->move('storage/cars/', $file_name)) {
                        $car->image = $file_name;
                    }
                }
                $car->mark = $request->input('brand_id');
                if($request->input('car_model'))
                    $car->car_model = $request->input('car_model');
                $car->plate =  $request->input('plate');
                $car->year =  $request->input('year');
                $car->color_id =  $request->input('color_id');
                $car->update();
                $request->session()->flash('alert-success', __('message.car_and_driver_updated'));
                return redirect($request->input('back_link'));
            }else{
                $request->session()->flash('alert-danger',  __('message.car_not_updated'));
                return redirect()->back();
            }

        //return redirect('admin/drivers/edit/'.$driver->id);

    }

    public function destroy($id)
    {
        $driver = Driver::find($id);
        if($driver )
        {

            $user = User::find($driver->user_id)->delete();
            $car = Car::where('driver_id',$driver->user_id)->delete();

            $del_driver = Driver::find($id)->delete();
        }
        return back()->with('success','Driver deleted successfully');
    }

    public function finalDelete($id)
    {
        $driver = Driver::find($id);
        if($driver )
        {
            //echo "gg";
            $user = User::find($driver->user_id)->forcedelete();
            $car = Car::where('driver_id',$driver->user_id)->forcedelete();

            $del_driver = Driver::find($id)->forcedelete();
        }
        return back()->with('success','Driver deleted successfully');
    }

    public function driversHaveTrips(){
        $title =__('setting.Drivers_have_trip');
        $U = new User();
        $drivers = $U->getDriverswithActiveTrip();
        $det = array();
        foreach ($drivers  as $driver)
        {
            $x = User::find($driver->id)->drivers_details;
            array_push($det , $x);
        }
        //var_dump($det);exit();
        return view('admin.drivers.driversHaveTrips',compact('drivers','det','title'));
    }


    public function changePassword($id)
    {
        $title = __('menus.change_password');
        $driver = Driver::with('driver_as_user')->findOrFail($id);
        //var_dump($driver);exit();
        //$car = Car::where('driver_id',$driver->user_id)->first();
        //$car_model = Car_model::find($car->car_model);
        //$car_types = Car_type::all();
        //$colors = Color::all();
        return view('admin.drivers.changePassword',compact('driver','title'));
    }

    public function changePasswordUpdate(Request $request)
    {
        $driver = Driver::findOrFail($request->id);
        if($driver)
        {
           // echo $request->password_confirmation;
            $nums = rand(0001,9999);
            $capitalString = "ABCDEFGHIJKLMNOPQRSTUVWZYZ";
            $smallString = "abcdefghijklmnopqrstuvwxyz";
            $specialCharacters = "@#$%";
            $capital = $capitalString[rand(0, strlen($capitalString)-1)];
            $small = $smallString[rand(0, strlen($smallString)-1)];
            $special = $specialCharacters[rand(0, strlen($specialCharacters)-1)];


            $password = $capital.$small.$nums.$special.$special;
            $hashedPassword = Hash::make($password);
            $user = User::find($driver->user_id);
            $user->password = $hashedPassword;
            $user->save();

            $sendSMS = new SendSMSController();
            $msg = 'New Password:  ' . $password;
            $sendSMS->send($msg,$user->phone );

            session()->flash('alert-success', __('message.password_changed'));
            return redirect('/admin/drivers');

        }
        else{
            session()->flash('alert-danger', __('message.No_data'));
            return redirect('/admin/drivers/change_password/'.$request->id);
        }
    }

    public function driversOnMap(){
        $key = env('GOOGLE_MAPS_API_KEY');
        $title = __('label.drivers_map');

        $coor = new Coordinate();
        $driverCoordinates = $coor->driversLastLocation( );

        $driversCoordinatesArray = array();
        $activeMap = true;
        $i=0;
        foreach ($driverCoordinates as $coordinte){
            //echo $coordinte->latitude;echo "<br>";
            $driversCoordinatesArray[$i] = [$coordinte->name,floatval($coordinte->latitude) , floatval($coordinte->longitude) ,$i+1 ];
            $i++;
        }
        //echo "<pre>";var_dump($driversCoordinatesArray);echo "</pre>";exit();

//        $driversCoordinatesArray1 = array();
//        $driversCoordinatesArray1 =
//                [
//            ['Bondi Beach', -33.890542, 151.274856, 4],
//            ['Coogee Beach', -33.923036, 151.259052, 5],
//            ['Cronulla Beach', -34.028249, 151.157507, 3],
//            ['Manly Beach', -33.80010128657071, 151.28747820854187, 2],
//            ['Maroubra Beach', -33.950198, 151.259302, 1]
//
//        ];
        return view('admin.drivers.drivers_on_map',compact('key','title','driversCoordinatesArray'));
    }
    public function test(){

       $u = new User();
//        $userIds = $u->getAllUsers()->pluck('id')->toArray();
//        var_dump($userIds);
//        $policy = Policy::find(1);
//        $policySTR = $policy->arabic_privacy." ".$policy->english_privacy;
//        var_dump($policySTR);
       // $x = new Sum_money();
//        $driversBalanceToday = $x->driversBalancesToday();
//        echo "<pre>";
//        var_dump($driversBalanceToday);
//        echo "</pre>";
       // $y = $x->driverBalance(365);
//      if($y)
//          echo "yes";
//        $df = User::where('id',365)->first();
//        //$df->sum_money
//        var_dump($df->balance->id);
//        $x= new Offers();
//        $f = $x->checkOffers(348);
//       // var_dump($f);
//        foreach ($f as $offer)
//            echo $offer->message;
        $r = $u->getDriversIdswithActiveTrip();
        var_dump($r);


    }





}
