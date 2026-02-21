<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        Property::firstOrCreate(
            ['slug' => 'casa-pupuya'],
            [
                'name' => 'Casa Pupuya',
                'airbnb_listing_id' => '16897504',
                'airbnb_url' => 'https://www.airbnb.com/rooms/16897504',
                'location' => 'Pupuya, O\'Higgins, Chile',
                'latitude' => -34.0000,
                'longitude' => -71.9500,
                'checkin_time' => '15:00',
                'checkout_time' => '12:00',
            ]
        );

        Property::firstOrCreate(
            ['slug' => 'cabana-pullinque'],
            [
                'name' => 'Cabaña Pullinque',
                'airbnb_listing_id' => '709559641189941784',
                'airbnb_url' => 'https://www.airbnb.com/rooms/709559641189941784',
                'location' => 'Pullinque, Panguipulli, Los Ríos, Chile',
                'latitude' => -39.6400,
                'longitude' => -72.3300,
                'checkin_time' => '15:00',
                'checkout_time' => '12:00',
            ]
        );
    }
}
