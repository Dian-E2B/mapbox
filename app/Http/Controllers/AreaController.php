<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function CheckIcons(Request $request)
    {
        $centers = DB::table('areas')
            ->select('center_lat', 'center_lng', 'isSprinkled')
            ->get()
            ->map(function ($area) {
                return [
                    'coords' => [$area->center_lng, $area->center_lat], // <-- MUST BE lng, lat
                    'label' => 'Polygon Center',
                    'isSprinkled' => $area->isSprinkled
                ];
            });

        return response()->json($centers);
    }


    public function sprinkol($id, $action)
    {
        if ($action === 'setSprinkol_add') {
            $updated = Area::where('id', $id)->update(['isSprinkled' => 1]);
            $data = ['updated' => $updated];
        } else {
            $data = ['updated' => false];
        }

        return response()->json($data);
    }
}
