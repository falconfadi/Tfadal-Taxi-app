<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class Offers extends Model
{
    use HasFactory;
    use LogsActivity;

    public function users()
    {
        return $this->belongsToMany(User::class,'offers_users','offer_id','user_id' );
    }
    public function checkOffers($user_id){
        $offers = Offers::wherehas('users',function($q) use($user_id){
            $q->where('user_id',$user_id);
        })
            ->whereDate('end_time','>=',Carbon::today())
           ->whereDate('start_time','<=',Carbon::today())
            ->get();
//        $c = User::where('id',$user_id)->first();
//            $offers = $c->offers()->get();

           // $offers = Offers::users()->where('user_id',$user_id)->get();
        return $offers;
    }

    public function checkOffersOfNewUser($user_id){
        $offers = $this->wherehas('users',function($q) use($user_id){
            $q->where('user_id',$user_id);
        })
            ->where('end_time','>=',Carbon::now()->timestamp)
            ->where('is_new_client',1)
            ->first();
        return $offers;
    }


    public function getOfferByCode($user_id,$code){
        $offer = $this->wherehas('users',function($q) use($user_id){
            $q->where('user_id',$user_id);
        })
        ->whereDate('end_time','>=',Carbon::today())
        ->whereDate('start_time','<=',Carbon::today())
        ->where('code',$code)

        ->orWhere(function($q) use($code){
            $q->Where('is_all',1)
                ->whereDate('end_time','>=',Carbon::today())
                ->whereDate('start_time','<=',Carbon::today())
                ->where('code',$code);
        })
        ->first();
        return $offer;
    }

    public function checkDiscountAvailabilty($user_id){
        //if the user insert code
        $offer = $this->wherehas('users',function($q) use($user_id){
            $q->where('user_id',$user_id);
        })
        ->orWhere('is_all',1)
        ->whereDate('end_time','>=',Carbon::today())
        ->whereDate('start_time','<=',Carbon::today())
        ->first();
        if($offer){
            $codeInserted = OffersCodeTaken::where('offer_id',$offer->id)->where('user_id',$user_id)->first();
            if($codeInserted){
                //amount not ratio
                return $offer->discount;
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }

    public function getActivitylogOptions():LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['details'])
            /*->logOnlyDirty()*/;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->causer_id = Auth::guard('admin')->user()->getAuthIdentifier();
    }
}
