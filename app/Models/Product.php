<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','category','slug','description','price','quantity'];
    protected $guarded = ['id'];
}
