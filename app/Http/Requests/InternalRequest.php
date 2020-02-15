<?php

namespace Blegrator\Http\Requests;

class InternalRequest
{
    public static function toArray($data)
    {
        return json_decode(app()->handle($data)->getContent(), true);
    }
}
