<?php

namespace App\Traits;

trait ApiResponser
{
    protected function success($data = [], $message = "")
    {
        //set header to json
        header('Content-Type: application/json');
        return json_encode([
            'code' => 1,
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function error($message = "")
    {
        header('Content-Type: application/json');
        return json_encode([
            'code' => 0,
            'message' => $message,
            'data' => ""
        ]);
    }
}
