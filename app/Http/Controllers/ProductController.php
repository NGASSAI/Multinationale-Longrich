<?php

namespace App\Http\Controllers;
// $fillable = ['name','category','slug','description','price','quantity'];


use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(){
        $products =Product::where('id',3)->delete();


        


        return $products;
    }
};
