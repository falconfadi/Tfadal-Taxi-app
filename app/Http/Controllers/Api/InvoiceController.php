<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\KpiDriversController;
use App\Http\Controllers\Admin\KpiUsersController;
use App\Http\Controllers\Admin\SumMoneyController;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Invoice;

use App\Models\Notification;
use App\Models\Offers;
use App\Models\Trip;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    public function __construct()
    {
        //parent::__construct();

        date_default_timezone_set('Asia/Damascus');
    }
    //api calculate_invoice
    public function calculateInvoice(Request $request)
    {
        $request->validate([
            'trip_id' =>'required',
        ]);

        //calculate distance
        //calculate time
        $trip = Trip::with('carType')->where('id',$request->trip_id)->first();
        if ($trip)
        {
            $price = 0;
            //$carObj = new CarController();

            //$distance = $carObj->googleMapDistance($trip->latitude_from, $trip->longitude_from, $trip->latitude_to, $trip->longitude_to);
            $startTime = Carbon::parse($trip->start_date);
            $endTime = Carbon::now();
            $endTime->diffInMinutes($startTime);
            $realDuration = $endTime->diffInMinutes($startTime);
            $expectedDuration = $realDuration;

            $now = date('Y-m-d H:i:s');
            $priceAndDiscount = $this->calculatePrice($request->trip_distance  ,$expectedDuration, $trip);
            $price = round($priceAndDiscount[0],2);
            $invoice = new Invoice();
            $invoice->trip_id = $request->trip_id;
            $invoice->discount = $priceAndDiscount[1];
            $invoice->price = $price;

            $invoice->invoice_number = $invoice->getLastInvoiceNumber();
            $invoice->save();

            // KPI....some money achieved...user
            $x = new KpiUsersController();
            $x->sumMoneyPaid($trip->user_id , $price);

            // KPI ....some money achieved...driver
            $y = new KpiDriversController();
            $y->sumMoneyAchieved($trip->driver_id , $price);



            //calculate money per day for driver
            $companyPercentage = $invoice->getCompanyPercentage($price);
            $z = new SumMoneyController();
            $z->sumMoneyAcheivedPerDay($trip->driver_id,$companyPercentage);

            $totalPrice = $price + $priceAndDiscount[1];

            //send notification to user
            $notificationObj = new NotificationsController();
            $data = [
                'trip_id'=> $trip->id,
                'notification_type'=>'trip',
                'is_driver' => 0,
                'time' => round($expectedDuration, 2),
                'distance'=>round($request->trip_distance, 2),
                'price_before_discount' => $totalPrice ,
                'net_price'=>$price,
                'discount' =>$priceAndDiscount[1],
            ];


            $title = "فاتورة الرحلة";
            $body = "<h2>";
            $body .= "تفاصيل الفاتورة ";
            $body .= "<br>";
            $body .= " القيمة الكلية:  ".$totalPrice;
            $body .= "<br>";
            $body .= " المسافة المقطوعة:  ".round($request->trip_distance, 2);
            $body .= "<br>";
            $body .= " الحسومات:  ".$priceAndDiscount[1];
            $body .= "<br>";
            $body .= " المبلغ الصافي:  ".$price;
            $body .= "</h2>";

            $notificationObj->sendNotifications($trip->user_id, $title, $body, $data);
            $x = new  Notification();
            $x->saveNotification([$trip->user_id],$title,2, $body,0,0);
            return response()->json([
                "success" => true,
                "message" => "Invoice",
                'data'=> [
                    'time' => round($expectedDuration, 2),
                    'distance'=>round($request->trip_distance, 2),
                    'price_before_discount' => $totalPrice ,
                    'net_price'=>$price,
                    'discount' =>$priceAndDiscount[1],
                ]
            ]);
        }
        else {
            return response()->json([
                "success" => false,
                'data' => [
                    'arabic_error' => 'لا يوجد',
                    'english_error' => ' No trip!!',
                    'arabic_result' => '',
                    'english_result' => '',
                ]
            ]);
        }
    }

    //get all invoices for  a user or driver
    public function invoicesByUser(Request $request)
    {
        $request->validate([
            'user_id' =>'required',
        ]);

        $user = User::find($request->user_id);
        $invoices = array();
        if($user->is_driver==0)
        {
            $invoices = Invoice::whereHas('trip',function($subQ) use ($request){
                $subQ->where('user_id',$request->user_id);
            })->with('trip')->get();
        }
        else{
            $invoices = Invoice::whereHas('trip',function($subQ) use ($request){
                $subQ->where('driver_id',$request->user_id);
            })->with('trip')->get();
        }

        if ($invoices)
        {
            $total_amount = 0;
            foreach($invoices as $invoice)
            {
                $total_amount += $invoice->price;
                $invoice->driver_name = (User::find($invoice->trip->driver_id))?User::find($invoice->trip->driver_id)->name:'';
            }

            return response()->json([
                "success" => true,
                "message" => "Invoices",
                'data'=> [
                    'invoices' => $invoices,
                    'total_amount' => round($total_amount,2)
                ]
            ]);
        }
        else {
            return response()->json([
                "success" => false,
                'data' => [
                    'arabic_error' => 'لا يوجد',
                    'english_error' => ' No invoice!!',
                    'arabic_result' => '',
                    'english_result' => '',
                    'total_amount' =>0
                ]
            ]);
        }
    }

    public function getDifferenceOfTimeInMinutes($start_time, $end_time)
    {

        //'2020-02-10 04:04:26'
        //'2020-02-11 04:36:56'
        $minutes = abs(strtotime($end_time) - strtotime($start_time)) / 60;

        return $minutes;
    }

    public function calculatePrice($distance  ,$expectedDuration, $trip)
    {

        $price = 0;
        //$oldPrice = round($distance *  $trip->carType->price ,2)+ $googleCost;
        //$oldPrice = $this->calculateInvoice($distance, )
        $T = new TripController();
        $oldPrice = $T->estimatePrice($distance, $expectedDuration, $trip);
        //check if there is offer
        $O = new Offers();
        $discount = $O->checkDiscountAvailabilty($trip->user_id);
        $result = (float)($oldPrice-$discount);
        $price = (($result)>0)?$result:0;
        $priceAndDiscount = array();
        //price
        $priceAndDiscount[0] = $price;
        //discount
        $priceAndDiscount[1] = $discount;
        return $priceAndDiscount;
    }

    public function test()
   {


//        $car = Car::where('car_type',6)->first();
//        if($car)
//            echo "go";
//        else
//            echo "not";

        $trip = Trip::with('trip_details')->find(470);
        var_dump($trip->trip_details->expected_distance);
    }
//    public function exchangeFromUSD($amount ,$to = 'SYP')
//    {
//        $curl = \curl_init();




}
