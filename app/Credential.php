<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    protected $fillable = [

         'name', 'client_name', 'url', 'username', 'password', 'client_id', 'client_secret', 'access_token', 'refresh_token'

     ];
         
}
