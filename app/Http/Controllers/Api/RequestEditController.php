<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request_edit;
use Illuminate\Http\Request;

class RequestEditController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sendEditCarRequest(Request $request)
    {
        $request->validate([
            'driver_id' => 'required',
        ]);

        $x = new Request_edit();
        $x->accepted = 0;
        $x->driver_id = $request->driver_id;

        if ($x->save()) {
            return response()->json(
                [
                    'message' => 'Sending Request',
                    'data' => [
                        'arabic_error' => '',
                        'english_error' => '',
                        'arabic_result' => ' تم إرسال طلب تعديل',
                        'english_result' => 'Request Sent',
                    ]
                ]
            );
        } else {
            return response()->json(
                [
                    'message' => 'Sending Request',
                    'data' => [
                        'arabic_error' => 'لم يتم ',
                        'english_error' => 'Request Not Sent',
                        'arabic_result' => '',
                        'english_result' => '',
                    ]
                ]
            );
        }

    }

}
