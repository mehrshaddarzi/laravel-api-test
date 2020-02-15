<?php

use Illuminate\Database\Seeder;

class CemeterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(storage_path('cemetery/lists.json'));
        $region = json_decode($json, true);
        foreach ($region as $name => $city) {
            // Add Region To DB
            $region_id = DB::table('regions')->insertGetId([
                'name' => $name
            ]);

            // Create List Of City
            foreach ($city as $city_name => $cemetery_list) {
                $city_id = DB::table('cities')->insertGetId([
                    'region_id' => $region_id,
                    'name' => $city_name
                ]);

                // Add List Of Cemetery For City
                foreach ($cemetery_list as $cemetery) {
                    if (!empty(trim($cemetery['lat']))) {
                        DB::table('cemeteries')->insert([
                            'city_id' => $city_id,
                            'name' => $cemetery['name'],
                            'lat' => $cemetery['lat'],
                            'long' => $cemetery['lng'],
                            'address' => $cemetery['address'],
                        ]);
                    }
                }
            }
        }
    }
}
