<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    //get revenue
    public function getMoneyBetweenTwoDates( $start_date, $end_date){
        $startDate = Carbon::createFromFormat('Y-m-d', $start_date);
        $endDate = Carbon::createFromFormat('Y-m-d',  $end_date);

//        $ins = Invoice::whereDate('created_at', '>=', $startDate)->
//        whereDate('created_at', '<=', $endDate )/*->whereHas('trip',function($subQ) {
//            $subQ->where('freeze',0);
//        })*/->with('trip')->get();
//        return $ins;

        $ins = DB::table('invoices')
            ->select(DB::raw('sum(price) as sum_money, DATE(created_at) as trip_day'))
            ->whereDate('created_at', '>=', $startDate )
            ->whereDate('created_at', '<=', $endDate )
           /* ->where('status', '!=',5 )*/
            ->groupBy('trip_day')
            ->get();
           return $ins;

    }


    public function getLastInvoiceNumber(){
        $latest =  DB::table('invoices')->latest('invoice_number')->first()->invoice_number ?? 100000;
        $latest = (int)$latest+1;
        return $latest;
    }

    public function getCompanyPercentage($price){
        $x = new Setting();
        $company_percentage = $x->getSetting()->company_percentage;
        $result = ($company_percentage/100)*$price;
        return $result;
    }
}
