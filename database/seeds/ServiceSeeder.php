<?php

use Blegrator\Commission;
use Blegrator\Servicetype;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Default Service Type
        $service_id = DB::table('servicetypes')->insertGetId(
            [
                'user_id' => DB::table('users')->first()->id,
                'name' => 'Gravestone'
            ]
        );

        // Create_services_list
        DB::table('services')->insert([
            [
                'name' => 'HAUTAKIVEN SIIRTO',
                'servicetype_id' => $service_id,
                'icon' => 'Hautakiven_siirto.png',
                'avg_price' => '163,00 € – 451,00 €',
                'is_active' => true
            ],
            [
                'name' => 'PALVELUPAKETIT',
                'servicetype_id' => $service_id,
                'icon' => 'palvelupaketit.png',
                'avg_price' => '347,00 € – 670,00 €',
                'is_active' => true
            ],
            [
                'name' => 'UUDET TEKSTIT',
                'servicetype_id' => $service_id,
                'icon' => 'uudet-tekstit.png',
                'avg_price' => '331,00 € – 782,00 €',
                'is_active' => true
            ],
            [
                'name' => 'HUOLENPITOSOPIMUKSET',
                'servicetype_id' => $service_id,
                'icon' => 'huolenpitopalvelu.png',
                'avg_price' => '29,00 € / vuosi',
                'is_active' => true
            ],
            [
                'name' => 'VANHAN TEKSTIN KUNNOSTUS',
                'servicetype_id' => $service_id,
                'icon' => 'kultaus-b.png',
                'avg_price' => '217,00 € – 540,00 €',
                'is_active' => true
            ],
            [
                'name' => 'HAUTAKIVEN OIKAISU',
                'servicetype_id' => $service_id,
                'icon' => 'oikaisutakuu.png',
                'avg_price' => '179,00 €',
                'is_active' => true
            ],
            [
                'name' => 'HAUTAKIVEN PESU',
                'servicetype_id' => $service_id,
                'icon' => 'pesu.png',
                'avg_price' => '105,00 € – 252,00 €',
                'is_active' => true
            ],
            [
                'name' => 'MUUT PALVELUT',
                'servicetype_id' => $service_id,
                'icon' => 'muut-palvelut.png',
                'avg_price' => '',
                'is_active' => true
            ]
        ]);

        // Create Service User
        DB::table('service_user')->insert([
            [
                'service_id' => 1,
                'user_id' => 3,
            ],
            [
                'service_id' => 2,
                'user_id' => 3,
            ],
        ]);

        // Add Service Item
        DB::table('service_item')->insert([
            [
                'service_id' => 1,
                'user_id' => 3,
                'name' => 'name',
                'excerpt' => 'lorem',
                'description' => 'desc',
                'is_active' => true,
                'period' => 0,
                'price' => 100,
            ],
            [
                'service_id' => 1,
                'user_id' => 3,
                'name' => 'name2',
                'excerpt' => 'lorem2',
                'description' => 'desc2',
                'is_active' => true,
                'period' => 12,
                'price' => 200,
            ],
            [
                'service_id' => 1,
                'user_id' => 3,
                'name' => 'name3',
                'excerpt' => 'lorem3',
                'description' => 'desc3',
                'is_active' => false,
                'period' => 5,
                'price' => 180,
            ],
            [
                'service_id' => 2,
                'user_id' => 3,
                'name' => 'name',
                'excerpt' => 'lorem',
                'description' => 'desc',
                'is_active' => true,
                'period' => 5,
                'price' => 180,
            ]
        ]);


//        factory(Servicetype::class)->create(['name' => 'ServiceType 2'])->each(function () {
//            factory(Commission::class, 2)->create();
//        });
    }
}
