<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Travel;
use App\Enums\TravelStatus;
use App\Http\Resources\TravelResource;
use App\Exceptions\AlreadyDriverException;
use App\Http\Requests\DriverSignupRequest;
use App\Http\Requests\DriverUpdateRequest;

class DriverController extends Controller
{
    public function signup(DriverSignupRequest $request)
    {
        $this->authorize('signup');

        if (Driver::isDriver(auth()->user())) {
            throw new AlreadyDriverException;
        }
        try {
            $request = $request->validated();
            $request['id'] = auth()->user()->id;
            $result = Driver::create($request);
            return apiResponseSuccess();
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }

    public function update(DriverUpdateRequest $request)
    {
        $this->authorize('update');

        try {
            $driver = Driver::findOrFail(auth()->user()->id);
            $requestDriver = $request->validated();
            $driver->update([
                'latitude'   => $requestDriver['latitude'],
                'longitude'  => $requestDriver['longitude'],
                'status'     => $requestDriver['status'],

            ]);
            $travels = Travel::whereStatus(TravelStatus::SEARCHING_FOR_DRIVER)->get();
            return new TravelResource($travels);
        } catch (\Throwable $th) {
            return apiResponseError($th->getMessage());
        }
    }


    public function view()
    {
        $driver = Driver::findOrFail(auth()->user()->id);
        return response()->json($driver);
    }
}
