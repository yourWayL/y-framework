<?php

namespace App\Client\Http\Api;

use App\Client\Http\Domain\Login as Service;

class Login
{
    public function index2($request, $resources)
    {
        $result['a'] = 1111;
        return anJson($result);
    }
}