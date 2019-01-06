<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
protected $table='chats';
protected $fillable=['id','name','sender','receiver'];
    public function sender()
    {
        return $this->hasMany('App\User','id','sender');
    }

    public function receiver()
    {
        return $this->hasMany('App\User','id','receiver');
    }
}
