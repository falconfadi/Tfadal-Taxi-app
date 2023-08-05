<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PointLocationController;
use App\Models\Area;
use App\Models\Border;
use App\Models\Car;
use App\Models\Car_model;
use App\Models\Car_type;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\Trip;
use App\Models\User;
use DateInterval;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Cast\Object_;
use stdClass;

class CarController extends Controller
{
    public function __construct()
    {
        //parent::__construct();
        date_default_timezone_set('Asia/Damascus');
    }
    public function all_cars()
    {
        $cars = Car::with('driver','car_type')->get();

        return response()->json(
            [
                'message'=>'cars',
                'data'=> [
                    'cars'=> $cars
                ]
            ]
        );
    }

    // get cars and prices according to distance
    public function get_cars_type_with_value(Request $request)
    {
        $request->validate([
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'destiniation_lat' => 'required',
            'destiniation_lng' => 'required',
        ]);

        //check if given location is in available area
        if(/*!$this->checkAvailableArea($request->destiniation_lat, $request->destiniation_lng)*/false )
        {
            return response()->json([
                "success" => false,
                'data' => [
                    'arabic_error' => 'منطقة غير متاحة',
                    'english_error' => 'Not Available area',
                    'arabic_result' => '',
                    'english_result' => '',
                ]
            ]);
        }
        else{
            //distance between start and target
            //time between drivers and start
            $distance = 0;
            $distanceAndDuration =  $this->googleMapDistance($request->pickup_lat, $request->pickup_lng, $request->destiniation_lat, $request->destiniation_lng);

            $distance =  floatval($distanceAndDuration[0]);
            $expectedDuration=  floatval($distanceAndDuration[1]);

            // $carTypes = Car_type::with('cars');
            $Car_type = new Car_type();
            $carTypes = $Car_type->getExistCarTypes($request->not_furniture);
            $carsValues = array();
            //$carsValue =  new stdClass();
            //$s = new Setting();
            //$openPrice = $s->getSetting()->price_open;
            foreach ($carTypes as $carType){

                $tripObj = new TripController();

                $estimatedPrice = $tripObj->preEstimatePrice($distance , $carType->price);

                $carsValue =  new stdClass();
                $carsValue->estimated_price = $estimatedPrice;
                $carsValue->image = $carType->image;
                $carsValue->passenger_number = ($carType->passenger_number)?$carType->passenger_number:4;
                $carsValue->english_title = $carType->name ;
                $carsValue->arabic_title = $carType->name_ar ;
                $carsValue->car_type_id = $carType->id;
                array_push($carsValues , $carsValue);
            }

            $c = new PointsController();
            $is_have_point_discount = false;
            $is_have_point_discount = $c->checkPointsAvailabilty($request->user_id);
            $dr = new User();
            $availableDrivers = $dr->getAvailableDrivers();
            return response()->json(
                [
                    'message'=>'Cars values',
                    'data'=> [
                        'carTypes'=>$carsValues,
                        'distance'=>$distance,
                        'expected_duration'=>$expectedDuration,
                        'time_to_arrive_driver' => ''/*ceil($this->timeToArriveDriver($request->pickup_lat, $request->pickup_lng))*/,
                        'num_of_available_drivers' => (count($availableDrivers)>0)?count($availableDrivers):0,
                        'is_have_point_discount' =>$is_have_point_discount
                    ]
                ]
            );
        }
    }

    // get cars and prices according to distance
    public function get_cars_type_with_value_multi(Request $request)
    {
        $request->validate([
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'destiniation_lat' => 'required',
            'destiniation_lng' => 'required',
        ]);

        //check if given point is in available area
        if(/*!$this->checkAvailableArea($request->destiniation_lat, $request->destiniation_lng)*/false )
        {
            return response()->json([
                "success" => false,
                'data' => [
                    'arabic_error' => 'منطقة غير متاحة',
                    'english_error' => 'Not Available area',
                    'arabic_result' => '',
                    'english_result' => '',
                ]
            ]);
        }
        else{

            //distance between start and target
            //time between drivers and start
            $distance = 0;
            $distanceAndDuration =  $this->googleMapDistance($request->pickup_lat, $request->pickup_lng, $request->destiniation_lat, $request->destiniation_lng);

            $distanceArr = array();
            $distanceArr[] =  floatval($distanceAndDuration[0]);
            $expectedDuration=  floatval($distanceAndDuration[1]);
            $move_num = 2;
            $latitude_item = "latitude_stop_".$move_num;
            $longitude_item = "longitude_stop_".$move_num;
            $pickup_lat = $request->destiniation_lat;
            $pickup_lng = $request->destiniation_lng;
            $stop_locations = $request->stop_locations;
            while($move_num<=5 && $stop_locations[$latitude_item] &&  $stop_locations[$latitude_item]!= 0  ){
                if($move_num==6){
                    break;
                }else{
                    $destiniation_lat = $stop_locations[$latitude_item];
                    $destiniation_lng = $stop_locations[$longitude_item];

                    $distanceAndDuration =  $this->googleMapDistance($pickup_lat, $pickup_lng, $destiniation_lat, $destiniation_lng);
                    $distanceArr[] =  floatval($distanceAndDuration[0]);
                    $move_num++;
                    $latitude_item = "latitude_stop_".$move_num;
                    $longitude_item = "longitude_stop_".$move_num;
                    $pickup_lat = $destiniation_lat;
                    $pickup_lng = $destiniation_lng;
                }
            }
            $all_distances = array_sum($distanceArr);
            // $carTypes = Car_type::with('cars');
            $Car_type = new Car_type();
            $carTypes = $Car_type->getExistCarTypesWithoutFurniture();
            $carsValues = array();
            $carsValue =  new stdClass();

            foreach ($carTypes as $carType){
                $tripObj = new TripController();
                $estimatedPrice = $tripObj->preEstimatePrice($all_distances , $carType->price);
                $carsValue =  new stdClass();
                $carsValue->estimated_price = $estimatedPrice;
                $carsValue->image = $carType->image;
                $carsValue->passenger_number = 4;
                $carsValue->english_title = $carType->name ;
                $carsValue->arabic_title = $carType->name_ar ;
                $carsValue->car_type_id = $carType->id;
                array_push($carsValues , $carsValue);
            }

            $c = new PointsController();
            $is_have_point_discount = false;
            $is_have_point_discount = $c->checkPointsAvailabilty($request->user_id);
            $dr = new User();
            $availableDrivers = $dr->getAvailableDrivers();
            return response()->json(
                [
                    'message'=>'Cars values',
                    'data'=> [
                        'carTypes'=>$carsValues,
                        'distance'=>$all_distances,
                        'expected_duration' =>$expectedDuration,
                        'time_to_arrive_driver' => ''/*ceil($this->timeToArriveDriver($request->pickup_lat, $request->pickup_lng))*/,
                        'num_of_available_drivers' => (count($availableDrivers)>0)?count($availableDrivers):0,
                        'is_have_point_discount' =>$is_have_point_discount
                    ]
                ]
            );
        }
    }


    public function timeToArriveDriver($lat , $long)
    {
        $X = new User();
        $drivers = $X->getDrivers();
        $distances = array();
        foreach ($drivers as $nearestDriver){
            if( $nearestDriver->latitude != 0 && is_numeric($nearestDriver->latitude) && $nearestDriver->longitude != 0 && is_numeric($nearestDriver->longitude)){
                $x = $this->googleMapDistance($lat , $long , $nearestDriver->latitude , $nearestDriver->longitude);
                //echo $nearestDriver->id;echo "\n";
                if($x[2]=='OK')
                array_push($distances , (float)$x[0]) ;
            }
        }

        sort($distances);
        //take first 4 from drivers distance
        $distance = array_slice($distances, 0, 4);
        //get avg
        $distance = array_filter($distance);

        if(count($distance)!=0)
        $average = round(array_sum($distance)/count($distance),2);
        else $average = 0 ;
        return $average;
    }

    function isValidLongitude($longitude){
        $length =  strlen(substr(strrchr($longitude, "."), 1));
        if (preg_match("/^(\+|-)?((\d((\.)|\.\d{1,6})?)|(0*?\d\d((\.)|\.\d{1,6})?)|(0*?1[0-7]\d((\.)|\.\d{1,6})?)|(0*?180((\.)|\.0{1,6})?))$/", $longitude) &&  $length>=6) {
            return true;
        } else {
            return false;
        }
    }
    function isValidLatitude($lat){
        $length =  strlen(substr(strrchr($lat, "."), 1));
        if (preg_match("/^(\+|-)?((\d((\.)|\.\d{1,6})?)|(0*?\d\d((\.)|\.\d{1,6})?)|(0*?1[0-7]\d((\.)|\.\d{1,6})?)|(0*?180((\.)|\.0{1,6})?))$/", $lat) &&  $length>=6) {
            return true;
        } else {
            return false;
        }
    }
    public function test(){
//        $distance_data = file_get_contents(
//            'https://maps.googleapis.com/maps/api/geocode/json?latlng=33.51171489897901,36.28331458755996&sensor=true&key=AIzaSyCVKA7xPwgTT9FK-UEHnzcGjS9VjDWlJn8'
//        );
//        $distanceDurtion = array();
//        $distance_arr = json_decode($distance_data);
//        var_dump($distance_arr);
        //32.626823, 36.1076233


        //33.51056363260809, 36.301892545810865 damascus
        //34.727873588280346, 36.727268313836994 homs
        //33.6682026364735, 36.359397232200564 Saydnaya
        //33.559943426941864, 36.22849162720375 hameh
        if($this->checkAvailableArea(34.727873588280346, 36.727268313836994))
            echo " available";
        else{
            echo "NOT available";
        }
    }

    function googleMapDistance($latitudeFrom=0, $longitudeFrom=0, $latitudeTo=0, $longitudeTo=0)
    {
        $latitudeFrom = floatval(trim($latitudeFrom));
        $longitudeFrom = floatval(trim($longitudeFrom));
        $latitudeTo = floatval(trim($latitudeTo));
        $longitudeTo = floatval(trim($longitudeTo));
        $from_latlong = $latitudeFrom.','.$longitudeFrom;
        $to_latlong = $latitudeTo.','.$longitudeTo;
        $googleApi  = env('GOOGLE_MAPS_API_KEY');

        $distance_data = file_get_contents(
            'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.$from_latlong.'&destinations='.$to_latlong.'&key='.$googleApi
        );
       $distanceDurtion = array();
       $distance_arr = json_decode($distance_data);

        foreach ( $distance_arr->rows[0] as $key => $element )  {
            //var_dump($element);
            //echo "\n";echo "--------";echo "\n";
            $distance = $element[0]->distance->text;
            //echo $element[0]->distance->text; echo "\n";
            $duration = $element[0]->duration->text;
            $status = $element[0]->status;
            // The matching ID
            //$id = $dests[$key];
            //echo $status;
            $distance = preg_replace("/[^0-9.]/", "",  $distance);
            $duration = preg_replace("/[^0-9.]/", "",  $duration);

            // to kilometers
            $distance = $distance * 1.609344;

            $distance = number_format($distance, 2, '.', '');
            $duration = number_format($duration, 1, '.', '');
            $distanceDurtion[0] = $distance ; $distanceDurtion[1] = $duration; $distanceDurtion[2] = $status;
            //echo $status."--";
        }
        return $distanceDurtion;
    }

    public function car_types()
    {
        $car_types = Car_type::all();

        return response()->json(
            [
                'message'=>'car_types',
                'data'=> [
                    'cars'=> $car_types
                ]
            ]
        );
    }

    public function checkAvailableArea($lat , $long){

        $areas  = Area::all();
        if($areas)
        {
            $x = false;
            foreach ($areas as $area){
                $pois = Border::where('area',$area->id)->orderBy('order_')->get();
                if(count($pois)!=0)
                {
                    $polygon = array();
                    foreach ($pois as $poi){
                        $areaPoint = $poi->latitude." ".$poi->longitude;
                        array_push($polygon, $areaPoint);
                    }
                    $lastPoint = $pois[0]->latitude." ".$pois[0]->longitude;
                    array_push($polygon,$lastPoint);
                    //print_r($polygon);echo "<br>";
                    $points = array($lat." ".$long);
                    $pointLocation = new PointLocationController();
                    foreach($points as $key => $point) {
                        $x = $pointLocation->pointInPolygon($point, $polygon) ;
                    }
                    if($x==true){
                        return $x;
                    }
                }
            }
            return $x;
        }
    }


    public function update(Request $request)
    {
        $messages = [
            'car_image.mimes' => 'خطأ في نمط الصورة',
            'car_image.max' => 'الرجاء التأكد من أن حجم الصورة المرفقة لايتجاوز 2 ميغابايت',
           ];
        $validator = Validator::make($request->all(), [
            'car_type_id' => 'required',
            'car_image'=> 'mimes:png,jpg|max:2048',
        ],$messages );

        if ($validator->fails())
        {
            return response()->json(
                [
                    'message'=>'Driver Register',
                    'data'=> [  'max'=> str_contains($validator->errors() ,'image')?$messages['car_image.max']:'',
                        'english_error'=>'This Data is not valid',
                    ]
                ]
            );
        }
        $car = Car::where('driver_id',$request->driver_id)->first();
        if ($file = $request->file('car_image')) {
            //store file into document folder
            $image = $file->store('public/cars');
            $image = str_replace("public/", "", $image);
            $car->image = $image;
        }
        $car->car_type = $request->car_type_id;
        $car->passenger_number = $request->passenger_number;
        $car->plate = $request->plate;
        $car->year = $request->year;
        $car->mark = $request->brand;
        $car->plate_city_id = $request->plate_city_id;

        if( is_numeric($request->car_model) && $request->car_model != 0){
            $car->car_model = $request->car_model;
        }else{
            //make a new car model
            $car_model = New Car_model();
            $car_model->model  = $request->model_name;
            $car_model->brand_id  = $request->brand_id;
            $car_model->save();
            $car->car_model = $car_model->id;
        }
        if ($car->save())
        {
            return response()->json([
                "success" => true,
                "message" => "successfully updated",
                'data'=> [
                    'arabic_error' =>'',
                    'english_error' =>'',
                    'arabic_result' => 'تم تعديل البيانات',
                    'english_result' => 'successfully uploaded',
                ]
            ]);
        }
        else {
            return response()->json([
                "success" => false,
                'data' => [
                    'arabic_error' => 'لم يتم تعديل البيانات',
                    'english_error' => 'Driver Not updated!!',
                    'arabic_result' => '',
                    'english_result' => '',
                ]
            ]);
        }
    }

    public function checkIfInsideCircle($centerLat, $centerLong, $driverLat, $driverLong, $radius){
        $longA     = $centerLong*(M_PI/180); // M_PI is a php constant
        $latA     = $centerLat*(M_PI/180);
        $longB     = $driverLong*(M_PI/180);
        $latB     = $driverLat*(M_PI/180);

        $subBA       = bcsub ($longB, $longA, 20);
        $cosLatA     = cos($latA);
        $cosLatB     = cos($latB);
        $sinLatA     = sin($latA);
        $sinLatB     = sin($latB);

        //in km
        $distance = 6371*acos($cosLatA*$cosLatB*cos($subBA)+$sinLatA*$sinLatB);
        //echo $distance ; //in km
        $distanceInMeter = $distance*1000;
        if($distanceInMeter <= $radius){
            return true;
        }else{
            return false;
        }

    }






}
