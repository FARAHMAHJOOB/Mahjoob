<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use App\Models\TravelSpot;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\TravelSpotStoreRequest;
use App\Exceptions\SpotAlreadyPassedException;

class TravelSpotController extends Controller
{
    public function arrived(Travel $travel, TravelSpot $spot)
    {
        $this->authorize('markAsArrived', $spot);

        if ($spot->arrived_at != null) {
            throw new SpotAlreadyPassedException;
        }

        $result = $spot->update([
            'arrived_at' => now(),
        ]);
        apiResponse($result);
    }

    public function store(Travel $travel, TravelSpotStoreRequest $request)
    {
        $this->authorize('create', $travel);

        $spotRequest = $request->validated();
        $spotRequest['travel_id'] = $travel->id;

        $newSpot = TravelSpot::create($spotRequest);
        apiResponse($newSpot);
    }

    public function destroy(Travel $travel, TravelSpot $spot)
    {

        $this->authorize('destroy', $spot);

        $result = $spot->delete();
        apiResponse($result);
    }


    public function view(Travel $travel, TravelSpot $spot)
    {
        return response()->json($spot);
    }
}
