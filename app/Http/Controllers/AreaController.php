<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller
{
    
  public function store(Request $request)
{
    $data = $request->validate([
        'coordinates' => 'required|array',
        'area' => 'required|numeric',
        'center.lng' => 'required|numeric',
        'center.lat' => 'required|numeric',
    ]);

    // Example save
   Area::create([
        'coordinates' => json_encode($data['coordinates']),
        'area' => $data['area'],
        'center_lng' => $data['center']['lng'],
        'center_lat' => $data['center']['lat'],
    ]);

    return response()->json(['success' => true]);
}
}