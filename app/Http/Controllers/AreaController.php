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
           'polygon_code' => 'required',
    ]);

    // Example save
   Area::create([
        'polygon_code' => $request->polygon_code ?? Str::uuid(),
        'coordinates' => json_encode($data['coordinates']),
        'area' => $data['area'],
        'center_lng' => $data['center']['lng'],
        'center_lat' => $data['center']['lat'],
    ]);

    return response()->json(['success' => true]);
}

 public function retreive(Request $request)
{
   return response()->json(Area::all());
   
}

public function check(Request $request)
{
    $area = Area::where('polygon_code', $request->polygon_code)->first();

    if ($area) {
        return response()->json([
            'exists' => true,
            'area' => $area
        ]);
    }

    return response()->json(['exists' => false]);
}
}