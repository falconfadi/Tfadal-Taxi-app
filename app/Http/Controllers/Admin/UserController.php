<?php

namespace App\Http\Controllers\Admin;

use App\Models\Driver;
use App\Models\Kpi_users;
use App\Models\Note;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {
        $title = __('menus.Users');
        //$users = User::where('is_driver',0)->paginate(5);//all();
        $u = new User();
        //$users = User::where('is_driver',0)->get();
        $users = $u->getAllUsers();


        return view('admin.users.index',compact('users'));
    }

    public function view($id)
    {
        $user = User::findOrFail($id);
        $x = Kpi_users::where('user_id',$user->id)->first();
        $sumTripsAcheived = ($x)?$x->sum_trip_acheived:0;
        if($user)
        {
            return view('admin.users.view',compact('user','sumTripsAcheived'));
        }
    }

    public function edit($id)
    {
        $title = __('menus.Edit_User');
        $user = User::findOrFail($id);
        //print_r($user);exit();
        return view('admin.users.edit',compact('user','title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_model  $car_model
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $messages = [
            'image.mimes' => trans('validation.mimes'),
            'image.max.file' => trans('validation.max.file'),
        ];
        $validator = Validator::make($request->all(), [
            'image' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
           /* 'password' => 'min:7',*/
        ],$messages);

        if ($validator->fails()) {
            return redirect('admin/users/edit/'.$request->user_id)
                ->withErrors($validator)
                ->withInput();
        }
        $user = User::find($request->user_id);

        $user->name = $request->input('name');
        $user->address = $request->input('address');
        $user->phone = $request->input('phone');
        $user->gender = $request->input('gender');
       //update morph
        $note = $user->note();
        $note->note = $request->input('note');
        $user->note()->update(['note'=>$request->input('note')]);


        if($request->hasFile('image'))
        {
            $file = $request->file('image');
            $file_name = md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
            if ($file->move('storage/users/', $file_name)) {
                $user->image = $file_name;
            }
        }
        if ( $user->update()){
            $request->session()->flash('alert-success', __('message.user_updated'));
            return redirect('admin/users');
        }else{
            $request->session()->flash('alert-danger', __('message.user_not_updated'));
            return redirect('admin/users');
        }
    }

    public function changePassword($id)
    {
        $title = __('menus.change_password');
        $user = User::findOrFail($id);
        return view('admin.users.changePassword',compact('user','title'));
    }

    public function changePasswordUpdate(Request $request)
    {
        $user = User::findOrFail($request->id);
        if($user)
        {
            $nums = rand(0001,9999);
            $capitalString = "ABCDEFGHIJKLMNOPQRSTUVWZYZ";
            $smallString = "abcdefghijklmnopqrstuvwxyz";
            $specialCharacters = "@#$%";
            $capital = $capitalString[rand(0, strlen($capitalString)-1)];
            $small = $smallString[rand(0, strlen($smallString)-1)];
            $special = $specialCharacters[rand(0, strlen($specialCharacters)-1)];

            $password = $capital.$small.$nums.$special.$special;
            $hashedPassword = Hash::make($password);
            $user->password = $hashedPassword;
            $user->save();

            $sendSMS = new SendSMSController();
            $msg = 'New Password:  ' . $password;
            $sendSMS->send($msg,$user->phone );
            session()->flash('alert-success', __('message.password_changed'));
            return redirect('/admin/users');

        }
        else{
            session()->flash('alert-danger', __('message.No_data'));
            return redirect('/admin/users/change_password/'.$request->id);
        }
    }

    public function addNote(Request $request)
    {
        $user = User::find($request->user_id);
        if($user)
        {
            $note = new Note();
            $note->note = $request->note;
            $user->note()->save($note);

            session()->flash('alert-success', __('message.note_added'));
            return redirect('/admin/users');
        }
        else{
            session()->flash('alert-danger', __('message.No_data'));
            return redirect('/admin/users/');
        }
    }

    public function destroy($id)
    {
        $res = User::find($id)->delete();
        return back()->with('success','User deleted successfully');
    }
    public function finalDelete($id)
    {
        $res = User::find($id)->forcedelete();
        return redirect('admin/users');
    }

}
