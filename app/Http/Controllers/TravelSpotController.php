<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use App\Models\TravelSpot;
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

        try {
            $spot->update(['arrived_at' => now()]);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function store(Travel $travel, TravelSpotStoreRequest $request)
    {
        $this->authorize('create', $travel);

        try {
            $spotRequest = $request->validated();
            $spotRequest['travel_id'] = $travel->id;
            $newSpot = TravelSpot::create($spotRequest);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function destroy(Travel $travel, TravelSpot $spot)
    {

        $this->authorize('destroy', $spot);

        try {
            $result = $spot->delete();
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }


    public function view(Travel $travel, TravelSpot $spot)
    {
        return response()->json($spot);
    }
}
