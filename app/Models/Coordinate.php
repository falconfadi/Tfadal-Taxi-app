<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Coordinate extends Model
{
    use HasFactory;

    protected $fillable = [
        'longitude',
        'latitude',
        'driver_id'

    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // driver coordinate during specific trip
    public function driverCoordinatesDuringTrip($trip, $driver_id){
        $tripDate = Carbon::createFromFormat('Y-m-d H:i:s', $trip->trip_date);

        $results = Coordinate::where('driver_id',$driver_id)
            ->whereDate('created_at','>=', $tripDate)
            ->where('trip_id', $trip->id)
            ->orderBy('created_at')
            ->get();
        return $results;
    }
    //get connected drivers
    public function driversLastLocation(){
        //$tripDate = Carbon::createFromFormat('Y-m-d H:i:s', $trip->trip_date);
        //echo $driver_id;
        $results = DB::table('coordinates')
            ->select(DB::raw('MAX(coordinates.id) as id ,coordinates.latitude as latitude,coordinates.longitude as longitude,driver_id,name,last_name,father_name,drivers.id as driver_details_id'))
            ->join('users','coordinates.driver_id','=','users.id')
            ->join('drivers','drivers.user_id','=','users.id')
            ->where('is_connected',1)
            ->groupBy('driver_id')
        ->get();


//        $results = Coordinate::where('id',\DB::raw("(select max(`id`) as id from coordinates)"))
//            ->whereHas('driver',function($q) {
//                $q->where('is_connected',1);
//            })
//            ->groupBy('id')
//            ->get();
        return $results;
    }
}
