<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Travel;
use App\Models\TravelSpot;
use App\Enums\TravelStatus;
use App\Models\TravelEvent;
use Illuminate\Auth\Access\Response;
use App\Enums\TravelEventType;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\TravelResource;
use App\Exceptions\ActiveTravelException;
use App\Http\Requests\TravelStoreRequest;
use App\Exceptions\AllSpotsDidNotPassException;
use App\Exceptions\CannotCancelRunningTravelException;
use App\Exceptions\CarDoesNotArrivedAtOriginException;
use App\Exceptions\CannotCancelFinishedTravelException;

class TravelController extends Controller
{

    public function view(Travel $travel)
    {

        $this->authorize('view', $travel);
        return new TravelResource($travel);
    }

    public function store(TravelStoreRequest $request)
    {
        if (Travel::userHasActiveTravel(auth()->user())) {
            throw new ActiveTravelException;
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $newTravel['passenger_id'] = auth()->user()->id;
                $newTravel['status'] = TravelStatus::SEARCHING_FOR_DRIVER->value;
                $travel = Travel::create($newTravel);
                $spotRequest['travel_id'] = $travel->id;
                foreach ($request->spots as $spot) {
                    $spotRequest['position']  = $spot['position'];
                    $spotRequest['latitude']  = $spot['latitude'];
                    $spotRequest['longitude'] = $spot['longitude'];
                    $newSpot = TravelSpot::create($spotRequest);
                }
            });
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function cancel(Travel $travel)
    {
        $this->authorize('cancel', $travel);

        if ($travel->status == TravelStatus::DONE->value) {
            throw new CannotCancelFinishedTravelException();
        }

        if (Driver::isDriver(auth()->user())) {
            if ($travel->passengerIsInCar()) {
                throw new CannotCancelRunningTravelException;
            }
        } else {
            if ($travel->driverHasArrivedToOrigin() && $travel->driver != null) {
                throw new CannotCancelRunningTravelException;
            }
        }


        try {
            $result = $travel->update(['status' => TravelStatus::CANCELLED->value]);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function passengerOnBoard(Travel $travel)
    {
        $this->authorize('markAsPassengerOnBoard', $travel);

        if (!$travel->getOriginSpot()) {
            throw new CarDoesNotArrivedAtOriginException;
        }

        try {
            $event = TravelEvent::create([
                'type'      => TravelEventType::PASSENGER_ONBOARD->value,
                'travel_id' => $travel->id,
            ]);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function done(Travel $travel)
    {
        $this->authorize('markAsDone', $travel);

        if (!$travel->allSpotsPassed()) {
            throw new AllSpotsDidNotPassException;
        }
        try {
            $result = $travel->update([
                'status' => TravelStatus::DONE->value,
            ]);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function take(Travel $travel)
    {
        // $this->authorize('take');

        if (Travel::userHasActiveTravel(auth()->user())) {
            throw new ActiveTravelException;
        }

        try {
            $result = DB::transaction(function ()  use ($travel) {
                $acceptTravel   = $travel->update([
                    'driver_id' => auth()->user()->id,
                    'status'    => TravelStatus::RUNNING->value,

                ]);
                $event = TravelEvent::create([
                    'type'      => TravelEventType::ACCEPT_BY_DRIVER->value,
                    'travel_id' => $travel->id,
                ]);
            });
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }
}
