<?php

use Illuminate\Http\Exceptions\HttpResponseException;



    function apiResponseSuccess($messageSuccess = 'successfully!')
    {
        return response()->json(['hasError' => false, 'status' => 0, 'message' => $messageSuccess]);
    }

    function apiResponseError($messageError = 'An error occurred!!')
    {
        throw new HttpResponseException(
            response()->json([
                'hasError' => true,
                'message' => $messageError,
                'status' => 1
            ])
        );
    }
