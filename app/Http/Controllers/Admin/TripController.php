<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Controller;
use App\Models\Cancel_reason;
use App\Models\Cancel_reason_text;
use App\Models\Coordinate;
use App\Models\Invoice;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Rate_trip;
use App\Models\Trip;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\TripDetails;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $status = array();
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
        $this->status = array('0'=>__('menus.Pending'),
            '1'=>__('menus.Approved'),
            '2'=>__('menus.Arrived_to_customer'),
            '3'=>__('menus.In_the_way'),
            '4'=>__('menus.Arrived_to_destination_location'),
            '5'=>__('menus.Cancelled'),
            '6'=>__('menus.Scheduled_Trip'));
        App::setLocale('ar');
        session()->put('locale', 'ar');
    }

    public function index()
    {
        $title = 'Trips';
        $trips = Trip::all();
        $status = $this->status;
        $cancelReasons = Cancel_reason::all();

        return view('admin.trips.index',compact('trips','title','status','cancelReasons'));
    }

    public function canceled_trips()
    {
        $title = __('menus.Cancelled_trips');
        $trips = Trip::where('status',5)->get();

        return view('admin.trips.canceled_trips',compact('trips','title'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $key = env('GOOGLE_MAPS_API_KEY');
        $status = $this->status;
        $trip = Trip::find($id);
        //map
        $coor = new Coordinate();
        $driverCoordinatesArray = array();
        $centerLat = '33.52055680700218' ;$centerLon ='36.29604923082682';
        if($trip->driver){
            $driverCoordinates = $coor->driverCoordinatesDuringTrip($trip, $trip->driver->id);
            $i=0;

            $activeMap = true;
            foreach ($driverCoordinates as $coordinte){
                //echo $coordinte->latitude;echo "<br>";
                $driverCoordinatesArray[$i] = ['x',$coordinte->latitude, $coordinte->longitude,$i+1 ];
                $i++;
            }

            if(!empty($driverCoordinatesArray)){
                if(isset($driverCoordinatesArray[2])){
                    $centerLat = $driverCoordinatesArray[2][1];
                    $centerLon = $driverCoordinatesArray[2][2];
                }else{
                    $activeMap = false;
                }
            }
        }else{
            $activeMap = false;
        }

        //prices
        $tripDetails = TripDetails::where('trip_id',$trip->id)->first();
        if($tripDetails){
            $tripObj = new \App\Http\Controllers\Api\TripController();
            $expectedPrice = round($tripObj->estimatePrice($tripDetails->expected_distance ,$tripDetails->expected_duration, $trip),2);
        }
        else
            $expectedPrice = 0;
         $price = 0;

        $user = User::find($trip->user_id);
        $userGender = ($user->gender!=0)?($user->gender==1)?__('page.male'):__('page.female'):'';
        $driver = User::find($trip->driver_id);
        if($driver) {
            $driverGender =($user->gender!=0)?($user->gender==1)?__('page.male'):__('page.female'):'';
        }else{
            $driverGender = '---';
        }
        $timeToArriveDriver = ceil($trip->getDifferenceOfTimeInMinutes($trip->trip_date,$trip->arrive_to_customer_time));
        $timeToArriveCustomer = ceil($trip->getDifferenceOfTimeInMinutes($trip->arrive_to_customer_time,$trip->start_date));
        $tripDuration = ceil($trip->getDifferenceOfTimeInMinutes($trip->end_date,$trip->start_date));
        //the rate
        $rate = Rate_trip::where('trip_id',$id)->first();

        //final price
        $price = (Invoice::where('trip_id',$trip->id)->first())?Invoice::where('trip_id',$trip->id)->first()->price:0;
        $data = ['trip'=>$trip,
                'status'=>$status ,
                'key'=>$key ,
                'driverCoordinatesArray'=>$driverCoordinatesArray,
                'centerLat'=>$centerLat ,
                'centerLon'=>$centerLon,
                'activeMap'=>$activeMap ,
                'expectedPrice' =>$expectedPrice ,
                'price'=>$price,
                'timeToArriveDriver'=>$timeToArriveDriver ,
                'timeToArriveCustomer'=>$timeToArriveCustomer,
                'rate'=>$rate,
                'tripDuration'=>$tripDuration,
                'userGender'=>$userGender,
                'driverGender' => $driverGender];

        return view('admin.trips.view',$data);

    }

    public function destroy(Trip $trip)
    {
        //
    }


    public function pendingTrips(){
        $title =__('setting.pending_trips');

        $trips = Trip::with('user')->where('status',0)->where('driver_id',0)->get();
        $status = $this->status;
        $d = new User();
        $drivers = $d->getAvailableDrivers();
        return view('admin.trips.pending_trips',compact('trips','title','status','drivers'));
    }

    public function active_trips(){
        $title =__('setting.active_trips');

        $trips = Trip::with('driver','user')->whereIn('status',[1,2,3])->get();
        $status = $this->status;
        return view('admin.trips.active_trips',compact('trips','title','status'));
    }

    public function alertDriver(Request $request){
        //echo $tripId;exit();
        $title =__('setting.active_trips');

        $trip = Trip::find($request->trip_id);
        if($trip){
            $notificationObj = new NotificationsController();
            $data = [
                'trip_id'=> $request->trip_id,
                'notification_type'=>'advertisement',
                'is_driver' =>1
            ];
            $body = $request->text;
            $notificationObj->sendNotifications($trip->driver_id, "Warning", $body ,$data);
            Session::flash('alert-success', __('message.alert_driver'));
            return redirect('admin/trips/view/'.$request->trip_id);
        }
    }

    public function addDriver(Request $request)
    {
        //var_dump($request->all());exit();
        $trip = Trip::with('trip_details')->find($request->trip_id);

        $driver_id = $request->driver_id;
        $tripApi = new \App\Http\Controllers\Api\TripController();

        $preEstimatePrice = $tripApi->preEstimatePrice($trip->trip_details->expected_distance, $trip->carType->price);
        //$drivers[] = $key;
        $title =($trip->is_scheduled==1)?"رحلة مجدولة جديدة":"رحلة جديدة";
        $data = [
            'trip_id'=> $trip->id,
            'notification_type'=>'New Trip',
            'is_multiple'=>$trip->is_multiple,
            'is_driver' =>1,
            'add_features' =>1
        ];

        $body = "هناك طلب رحلة جديدة ";
        $body .= " - ";
        $body .= " مكان الانطلاق  ".$trip->location_from;
        $body .= " - ";
        $body .= " الوجهة  ".$trip->location_to;
        $body .= " - ";
        $body .= " المسافة  ".$trip->trip_details->expected_distance;
        $body .= " - ";
        $body .= " السعر  ".$preEstimatePrice;

        $notificationObj = new NotificationsController();
        $notificationObj->sendNotifications($driver_id, $title, $body ,$data);

        if(!empty($drivers)){
            $x = new  Notification();
            $x->saveNotification($drivers,$title,2, $body, 1,0);
        }

        Session::flash('alert-success', __('message.notification_sent'));
        return redirect('admin/pending_trips/');
    }

    public function cancelTrip(Request $request){
        //echo $request->trip_id;
        $trip = Trip::find($request->trip_id);
        if($trip->status==4){
            Session::flash('alert-danger', __('message.trip_already_ended'));
            return redirect('admin/trips/');
        }else{
            $cancelReasonText = new Cancel_reason_text();
            $cancelReasonText->user_id = 0;
            $cancelReasonText->trip_id = $request->trip_id;
            $cancelReasonText->reason_id = $request->reason_id;
            $cancelReasonText->reason_text = $request->reason_text;
            $cancelReasonText->is_admin = 1;
            if($cancelReasonText->save()){
                $trip->status = 5;
                $trip->update();
            }
            $notificationObj = new NotificationsController();
            $title = "إلغاء الرحلة";
            $body = "عذراً، تم إلغاء الرحلة من قبل الإدارة";
            $notificationObj->sendNotifications($trip->user_id , $title, $body,[ 'trip_id'=> $trip->id, 'notification_type'=>'trip',   'is_multiple'=>0,'is_driver' => 0 ]);
            $notificationObj->sendNotifications($trip->driver_id , $title, $body,[ 'trip_id'=> $trip->id, 'notification_type'=>'trip',   'is_multiple'=>0,'is_driver' => 1 ]);
            $x = new Notification();
            //will be back
            //fadi
            $x->saveNotification([$trip->user_id ],$title,2,$body, 0,0);
            $x->saveNotification([$trip->driver_id],$title,2,$body, 1,0);
            Session::flash('alert-success', __('message.trip_cancelled'));
            return redirect('admin/trips/');
        }
    }

    public function addNote(Request $request)
    {
        $trip = Trip::find($request->trip_id_);
        if($trip)
        {
            //morph
            $note = new Note();
            $note->note = $request->note;
            $trip->note()->save($note);
            session()->flash('alert-success', __('message.note_added'));
            return redirect('/admin/trips');
        }
        else{
            session()->flash('alert-danger', __('message.No_data'));
            return redirect('/admin/trips/');
        }
    }

    public function  search (Request $request){
        //ajax search
        $title = __('label.trips_search');
        $U = new User();
        $drivers = $U->getAllDrivers();
        $users = $U->getAllUsers();
        $trips = Trip::all();
        $driver_id = $request->get('driver_id');
        $user_id = $request->get('user_id');
        $type = $request->get('type');
        if ($request->ajax()) {
            $query = Trip::query();
            if($driver_id != 0)
                $query->where('driver_id',$driver_id );
            if($user_id != 0)
                $query->where('user_id',$user_id );
            if($type != 0){
                if($type == 1){
                    $query->where('is_scheduled',0 );
                    $query->where('is_multiple',0 );
                }elseif($type == 2){
                    $query->where('is_multiple',1 );
                }else{
                    $query->where('is_scheduled',1 );
                }
            }
            if($request->get('date'))
                $query->whereDate('start_date', $request->get('date'));
            $data = $query->get();
            $output = '';
            if (count($data) > 0) {
                $output = '';
                foreach ($data as $trip) {
                    $output .= '<tr>
                    <td><a href="'.url('admin/trips/view/'.$trip->id).'">'.$trip->serial_num.'</a></td>';
                    $url = ($trip->user)?url('admin/users/view/'.$trip->user->id):'#';
                    $name = ($trip->user)?$trip->user->name:'';
                    $output .= '<td><a href="'.$url.'">'.$name.'</a></td>';
                    $drivername = ($trip->driver)?$trip->driver->name:'';
                    $url = ($trip->driver)?url('admin/drivers/view/'.$trip->driver->id):'#';
                    $output .= '<td> <a href="'.$url.'">'.$drivername.'</a></td>';
                    $output .= '<td>'.$trip->start_date.'</td>';
                    $output .= '<td>'.$this->status[$trip->status].'</td>';
                    $output .= '<td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                     <i style="font-size:14px" class="fa fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">

                                    <a class="dropdown-item" href="'.url('admin/trips/view/'.$trip->id).'">
                                        <i data-feather="eye" class="mr-50"></i>
                                        <span>'.__("page.View").'</span>
                                    </a>';
                    $output .= '<a class="dropdown-item cancel-trip" data-toggle="modal" data-target="#cancel-trip-with-reason"  data-value="'.$trip->id.'"  >
                                            <i data-feather="stop-circle" class="mr-50"></i>
                                            <span>'.__('page.Cancel').'</span>
                                        </a>
                                        <a class="dropdown-item add_note" href="#" data-toggle="modal" data-target="#inlineForm" data-id="'.$trip->id.'">
                                            <i data-feather="clipboard"></i>
                                            <span>'.__('label.add_note').'</span>
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);">
                                            <i data-feather="trash" class="mr-50"></i>
                                            <span>'.__('page.Delete').'</span>
                                        </a>
                                    </div>
                                </div>
                        </td>';
                    $output .= '</tr>';
                }

            } else {
                $output .= '<li class="list-group-item">' . 'No results' . '</li>';
            }
            return $output;
        }
        return view('admin.trips.search',compact('drivers','users','title','trips'));
    }

}
