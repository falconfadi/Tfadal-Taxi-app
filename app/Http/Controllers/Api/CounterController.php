<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Trip;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function updateCounter(Request $request){

        $carObj = new CarController();
        $record = Counter::where('trip_id',$request->trip_id)->orderBy('id','desc')->limit(1)->first();
        $trip = Trip::find($request->trip_id);
        $counter = new Counter();
        $tripObj = new TripController();
        if($record){
            $distance = $carObj->googleMapDistance($record->latitude, $record->longitude, $request->latitude, $request->longitude);

            $counter->latitude = $request->latitude;
            $counter->longitude = $request->longitude;
            $counter->distance = $distance[0];
            $counter->counter = $record->counter+1;
            $counter->user_id = $request->driver_id;
            $counter->price =  round($tripObj->estimatePrice($distance[0] ,$distance[1], $trip),2) ;
            $counter->whole_price = (float)$counter->price + (float)$record->whole_price;
            $counter->whole_distance = (float)$distance[0] + (float)$record->whole_distance;
            $counter->trip_id = $request->trip_id;
            $counter->save();
        }
        else{
            //first count
            $distance = $carObj->googleMapDistance($trip->latitude_from, $trip->longitude_from, $request->latitude, $request->longitude);
            $counter->latitude = $request->latitude;
            $counter->longitude = $request->longitude;
            $counter->distance = $distance[0];
            $counter->counter = 0;
            $counter->user_id = $request->driver_id;
            $counter->price =  round($tripObj->estimatePrice($distance[0] ,$distance[1], $trip),2) ;
            $counter->whole_price = $counter->price ;
            $counter->whole_distance = $distance[0] ;
            $counter->trip_id = $request->trip_id;
            $counter->save();
        }
        return $counter;

    }
}
