<?php

namespace Blegrator\Services\Auth\Api;

class JWTAuth extends \Tymon\JWTAuth\JWTAuth
{
    use ExtendsJwtValidation;
}
