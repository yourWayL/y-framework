<?php

namespace App\Client\Http\Api;

use App\Client\Http\Domain\Login as Service;
use Secxun\Extend\Holyrisk\Handle\Rsa;
use Secxun\Extend\Holyrisk\Request;

class Login
{
    public function index2($request, $resources)
    {
        $result['a'] = 1111;
        $result = json_encode($result);
        return $result;
    }
}