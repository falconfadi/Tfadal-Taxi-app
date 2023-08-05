<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Kpi_users;
use App\Models\Offers;
use App\Http\Requests\StoreoffersRequest;
use App\Http\Requests\UpdateoffersRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class OffersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        $title = __('page.Offers');
        $offers = Offers::all();
        //take best 10 users
        $y = new Kpi_users();
        $bestUsersIds = array();
        $bestUsersIds = $y->best_users(10)->pluck('id')->toArray();


        $u = new User();
        $users = $u->getAllUsers();

        return view('admin.offers.index',compact('offers','title','bestUsersIds','users'));
    }
    /////////////////////////////////////////////////

    public function store(Request $request)
    {
      // var_dump($request->all());exit();
        $offer = new Offers();

        $offer->details = $request->input('details');
        $offer->start_time = $request->input('start_time');
        $offer->end_time = $request->input('end_time');
        $offer->points = $request->input('points');
        $offer->amount = ($request->input('amount'))?$request->input('amount'):0;
        $offer->discount =  ($request->input('discount'))?$request->input('discount'):0;
        $offer->code = ($request->input('code'))?$request->input('code'):'';
        $offer->num_of_trips = ($request->input('num_of_trips'))?$request->input('num_of_trips'):0;
        $offer->is_new_client = ($request->input('is_new_client'))?$request->input('is_new_client'):0;
        $offer->status = $request->input('status');
        $offer->is_all = ($request->input('all'))?$request->input('all'):0;
        $offerUsersIds =  $request->input('users');
        if ($offer->save()) {
            if($offerUsersIds)
            $offer->users()->attach($offerUsersIds);
            Session::flash('alert-success',__('message.offer_added'));
            return redirect('admin/offers');
        } else {

            Session::flash('message',__('message.offer_not_added'));
            return redirect('admin/offers');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\offers  $offers
     * @return \Illuminate\Http\Response
     */
    public function show(offers $offers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\offers  $offers
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $title = __('label.Edit_Offer');
        $offer = Offers::with('users')->where('id',$id)->first();
        $usersOffersIds = array();
        $usersOffersIds = $offer->users->pluck('id')->toArray();

        $u = new User();
        $users = $u->getAllUsers();

        return view('admin.offers.edit',compact('offer','title','users','usersOffersIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateoffersRequest  $request
     * @param  \App\Models\offers  $offers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $offer = Offers::with('users')->where('id',$request->id)->first();
        $oldUsersIds = $offer->users->pluck('id')->toArray();
        $offer->start_time = $request->input('start_time');
        $offer->end_time = $request->input('end_time');
        $offer->discount = $request->input('discount');
        $offer->amount = $request->input('amount');
        $offer->code = $request->input('code');
        $offer->details = $request->input('details');
        $offerUsersIds =  $request->input('users');
        if($offerUsersIds){
            $offer->users()->detach($oldUsersIds);
            $offer->users()->attach($offerUsersIds);
        }else{
            $offer->users()->detach($oldUsersIds);
        }

        if ($offer->update()) {
            Session::flash('alert-success',__('message.Offer_Edited'));
            return redirect('admin/offers/');
        } else {
            Session::flash('alert-success',__('message.Offer_not_edited'));
            return redirect('admin/offers/');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\offers  $offers
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = Offers::find($id)->delete();

        return back()->with('success','Offer deleted successfully');
    }

    public function test(){
        $c = new Offers();
        $x = $c->checkOffers(23);
        //var_dump($x);
        if($x)
        foreach ($x as $r){
            echo $r->details;
        }
    }
}
