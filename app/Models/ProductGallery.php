<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


class ProductGallery extends Model
{
    use HasFactory, SoftDeletes;

    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */

     protected $fillable=[
        'products_id',
        'url',
     ];

    //  function untuk dipakai di API 
    public function getUrlAttribute($url)
    {
        return config('app.url') . Storage::url($url);
    }
}