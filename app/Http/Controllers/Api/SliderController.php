<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offers;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function sliders()
    {
        $sliders = Slider::all();

        return response()->json(
            [
                'message'=>'sliders',
                'data'=> [
                    'sliders'=> $sliders
                ]
            ]
        );
    }

    public function slidersWithOffers(Request $request)
    {
        $sliders = Slider::all();
        //check offers
        $o = new Offers();
        $off = $o->checkOffers($request->user_id);
        $checkOffers =  ($off)?$off:false;
        return response()->json(
            [
                'message'=>'sliders',
                'data'=> [
                    'sliders'=> $sliders,
                    'checkOffers' => $checkOffers
                ]
            ]
        );
    }
}
