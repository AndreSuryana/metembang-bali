<?php

namespace App\Helpers;

class ResponseFormatter
{
    /**
     * Format success json response
     */
    public static function success($data = null, $message = null)
    {
        return response()->json([
            'status'  => 'success',
            'data'    => $data,
            'message' => $message
        ], 200);
    }

    /**
     * Format error json response
     */
    public static function error($message = null, $code = 500)
    {
        return response()->json([
            'status'  => 'error',
            'data'    => null,
            'message' => $message
        ], $code);
    }
}
