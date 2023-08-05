<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{


    protected $table = 'verification_code';
    use HasFactory;

    public function saveCode($user_id, $verficationCode){
        $lastVerificationCode = $this->where('user_id',$user_id)->first();
        if($lastVerificationCode)
        {
            $lastVerificationCode->verification_code = $verficationCode;
            $lastVerificationCode->save();

        }else{
            $this->user_id = $user_id;
            $this->verification_code = $verficationCode;
            $this->save();
        }

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

