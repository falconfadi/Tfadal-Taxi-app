<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sum_money extends Model
{
    use HasFactory;

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function driver_id() {
        $driver = $this->driver;
        return $driver->id;
    }

    public function sumMoneyAcheivedPerMonth($driver_id, $month, $year){
        $x = DB::select('select driver_id ,SUM(amount) as sum_amount
            from `sum_moneys`
            where `driver_id`='.$driver_id.' AND MONTH(work_day)='.$month.' AND YEAR(work_day)='.$year.' group by driver_id;');
        return $x;
    }

    public function driversBalancesToday(){

        $results = DB::table('sum_moneys')
            ->select(DB::raw('MAX(sum_moneys.id) as id ,work_day,amount ,driver_id,balance'))
            ->join('users','sum_moneys.driver_id','=','users.id')
            /*->join('drivers','drivers.user_id','=','users.id')*/
           /* ->where('is_connected',1)*/
            /*->whereDate('sum_moneys.created_at',Carbon::today())*/
            ->groupBy('driver_id')
            ->get();

        return $results;
    }
    public function driverBalance($driver_id){
            //get latest record
            $result = $this ->where('driver_id',$driver_id)
                ->latest()
                ->first();
        return $result;
    }


}
