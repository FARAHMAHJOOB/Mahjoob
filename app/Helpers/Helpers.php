<?php

use Illuminate\Http\Exceptions\HttpResponseException;


function apiResponse($data, $messageSuccess = 'successfully!', $messageError = 'An error occurred!!')
{
    if ($data) {
        return response()->json(['hasError' => false, 'status' => 0, 'message' => $messageSuccess]);
    } else {
        throw new HttpResponseException(response()->json([
            'hasError' => true,
            'message' => $messageError,
            'status' => 1
        ]));
    }
}
