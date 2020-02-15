<?php

namespace Blegrator\Transformers;

use Blegrator\City;
use Blegrator\Region;
use League\Fractal\TransformerAbstract;

class RegionTransformer extends TransformerAbstract
{
    public function transform(Region $region)
    {
        $array = [
            'id' => (int)$region->id,
            'name' => $region->name,
            'lat' => $region->lat,
            'long' => $region->long,
            'address' => $region->address,
            'photo' => (is_null($region->photo) ? null :
                url(Region::$Image_Path . '/' . $region->photo)
            )
        ];

        // Check Includes
        if (isset($_GET['includes'])) {
            $explode = explode(",", trim($_GET['includes']));

            // User
            if (in_array("city", $explode)) {
                $cities = $region->cities()->get();
                $city = [];
                foreach ($cities as $row) {
                    $item = $row;

                    // Cemetery
                    if (in_array("cemetery", $explode)) {
                        $item['cemetery'] = City::find($row->id)->cemetery()->get();
                    }

                    $city[] = $item;
                }

                $array['city'] = $city;
            }
        }

        return $array;
    }
}
