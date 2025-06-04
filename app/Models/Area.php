<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
   
    protected $fillable = ['coordinates', 'area', 'center_lng', 'center_lat'];
    protected $casts = [
        'coordinates' => 'array',
    ];
    use HasFactory;
}