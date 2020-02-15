<?php

namespace Blegrator\Transformers;

use Blegrator\Service;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract
{
    public function transform(Service $service)
    {
        $array = [
            'id' => (int)$service->id,
            'service_type_id' => (int)$service->servicetype_id,
            'name' => $service->name,
            'coverphoto' => (is_null($service->coverphoto) ? null :
                url(Service::$Image_Path . '/' . $service->coverphoto)
            ),
            'icon' => (is_null($service->icon) ? null : url(Service::$Image_Path . '/' . $service->icon)),
            'avg_price' => $service->avg_price,
            'active' => $service->is_active,
            'description' => $service->description,
            'commission_percentage' => $service->commission_percentage,
            'commission_desc' => $service->commission_desc,
        ];

        // Check Includes
        if (isset($_GET['includes'])) {
            $explode = explode(",", trim($_GET['includes']));

            // User
            if (in_array("user", $explode)) {
                $array['user'] = $service->users()->get();
            }

            // Service Item
            if (in_array("service_item", $explode)) {
                $array['service_items'] = $service->serviceitem()->get();
            }
        }

        return $array;
    }
}
