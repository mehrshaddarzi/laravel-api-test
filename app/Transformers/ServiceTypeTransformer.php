<?php

namespace Blegrator\Transformers;

use Blegrator\Service;
use Blegrator\Servicetype;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class ServiceTypeTransformer extends TransformerAbstract
{

    /**
     * List of resources to automatically include
     * @see https://fractal.thephpleague.com/transformers/
     *
     * @var array
     */
    protected $availableIncludes = [
        'service'
    ];

    public function transform(Servicetype $serviceType)
    {
        return [
            'id' => (int)$serviceType->id,
            'name' => $serviceType->name,
            'user_id' => $serviceType->user_id,
            'coverphoto' => (is_null($serviceType->coverphoto) ? null :
                url(Servicetype::$Image_Path . '/' . $serviceType->coverphoto)
            ),
            'icon' => (is_null($serviceType->icon) ? null : url(Servicetype::$Image_Path . '/' . $serviceType->icon)),
            'description' => $serviceType->description
        ];
    }

    /**
     * Include Service
     *
     * @param Servicetype $serviceType
     * @return \League\Fractal\Resource\Collection
     */
    public function includeService(Servicetype $serviceType)
    {
        $service = $serviceType->service;
        return $this->collection($service, new ServiceTransformer);
    }
}
