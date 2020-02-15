<?php

namespace Blegrator\Transformers;

use Blegrator\ServiceItem;
use League\Fractal\TransformerAbstract;

class ServiceItemTransformer extends TransformerAbstract
{
    public function transform(ServiceItem $serviceitem)
    {
        $array = [
            'id' => (int)$serviceitem->id,
            'service_id' => (int)$serviceitem->service_id,
            'user_id' => (int)$serviceitem->user_id,
            'name' => $serviceitem->name,
            'excerpt' => $serviceitem->excerpt,
            'description' => $serviceitem->description,
            'is_active' => (int)$serviceitem->is_active,
            'period' => (int)$serviceitem->period,
            'price' => (int)$serviceitem->price
        ];

        return $array;
    }
}
