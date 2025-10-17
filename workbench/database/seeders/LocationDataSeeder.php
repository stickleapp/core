<?php

namespace Workbench\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Models\LocationData;

class LocationDataSeeder extends Seeder
{
    const CITIES = [
        [
            'city' => 'New York',
            'country' => 'United States',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ],
        [
            'city' => 'London',
            'country' => 'United Kingdom',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
        [
            'city' => 'Tokyo',
            'country' => 'Japan',
            'latitude' => 35.6762,
            'longitude' => 139.6503,
        ],
        [
            'city' => 'Paris',
            'country' => 'France',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ],
        [
            'city' => 'Sydney',
            'country' => 'Australia',
            'latitude' => -33.8688,
            'longitude' => 151.2093,
        ],
        [
            'city' => 'Berlin',
            'country' => 'Germany',
            'latitude' => 52.5200,
            'longitude' => 13.4050,
        ],
        [
            'city' => 'Moscow',
            'country' => 'Russia',
            'latitude' => 55.7558,
            'longitude' => 37.6173,
        ],
        [
            'city' => 'Beijing',
            'country' => 'China',
            'latitude' => 39.9042,
            'longitude' => 116.4074,
        ],
        [
            'city' => 'Mumbai',
            'country' => 'India',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ],
        [
            'city' => 'São Paulo',
            'country' => 'Brazil',
            'latitude' => -23.5558,
            'longitude' => -46.6396,
        ],
        [
            'city' => 'Cairo',
            'country' => 'Egypt',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ],
        [
            'city' => 'Lagos',
            'country' => 'Nigeria',
            'latitude' => 6.5244,
            'longitude' => 3.3792,
        ],
        [
            'city' => 'Mexico City',
            'country' => 'Mexico',
            'latitude' => 19.4326,
            'longitude' => -99.1332,
        ],
        [
            'city' => 'Buenos Aires',
            'country' => 'Argentina',
            'latitude' => -34.6118,
            'longitude' => -58.3960,
        ],
        [
            'city' => 'Istanbul',
            'country' => 'Turkey',
            'latitude' => 41.0082,
            'longitude' => 28.9784,
        ],
        [
            'city' => 'Bangkok',
            'country' => 'Thailand',
            'latitude' => 13.7563,
            'longitude' => 100.5018,
        ],
        [
            'city' => 'Seoul',
            'country' => 'South Korea',
            'latitude' => 37.5665,
            'longitude' => 126.9780,
        ],
        [
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ],
        [
            'city' => 'Manila',
            'country' => 'Philippines',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ],
        [
            'city' => 'Tehran',
            'country' => 'Iran',
            'latitude' => 35.6892,
            'longitude' => 51.3890,
        ],
        [
            'city' => 'Baghdad',
            'country' => 'Iraq',
            'latitude' => 33.3152,
            'longitude' => 44.3661,
        ],
        [
            'city' => 'Riyadh',
            'country' => 'Saudi Arabia',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ],
        [
            'city' => 'Karachi',
            'country' => 'Pakistan',
            'latitude' => 24.8607,
            'longitude' => 67.0011,
        ],
        [
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
            'latitude' => 23.8103,
            'longitude' => 90.4125,
        ],
        [
            'city' => 'Yangon',
            'country' => 'Myanmar',
            'latitude' => 16.8661,
            'longitude' => 96.1951,
        ],
        [
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'latitude' => -1.2921,
            'longitude' => 36.8219,
        ],
        [
            'city' => 'Cape Town',
            'country' => 'South Africa',
            'latitude' => -33.9249,
            'longitude' => 18.4241,
        ],
        [
            'city' => 'Johannesburg',
            'country' => 'South Africa',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ],
        [
            'city' => 'Casablanca',
            'country' => 'Morocco',
            'latitude' => 33.5731,
            'longitude' => -7.5898,
        ],
        [
            'city' => 'Tunis',
            'country' => 'Tunisia',
            'latitude' => 36.8065,
            'longitude' => 10.1815,
        ],
        [
            'city' => 'Algiers',
            'country' => 'Algeria',
            'latitude' => 36.7538,
            'longitude' => 3.0588,
        ],
        [
            'city' => 'Rome',
            'country' => 'Italy',
            'latitude' => 41.9028,
            'longitude' => 12.4964,
        ],
        [
            'city' => 'Madrid',
            'country' => 'Spain',
            'latitude' => 40.4168,
            'longitude' => -3.7038,
        ],
        [
            'city' => 'Lisbon',
            'country' => 'Portugal',
            'latitude' => 38.7223,
            'longitude' => -9.1393,
        ],
        [
            'city' => 'Amsterdam',
            'country' => 'Netherlands',
            'latitude' => 52.3676,
            'longitude' => 4.9041,
        ],
        [
            'city' => 'Brussels',
            'country' => 'Belgium',
            'latitude' => 50.8503,
            'longitude' => 4.3517,
        ],
        [
            'city' => 'Vienna',
            'country' => 'Austria',
            'latitude' => 48.2082,
            'longitude' => 16.3738,
        ],
        [
            'city' => 'Zurich',
            'country' => 'Switzerland',
            'latitude' => 47.3769,
            'longitude' => 8.5417,
        ],
        [
            'city' => 'Prague',
            'country' => 'Czech Republic',
            'latitude' => 50.0755,
            'longitude' => 14.4378,
        ],
        [
            'city' => 'Warsaw',
            'country' => 'Poland',
            'latitude' => 52.2297,
            'longitude' => 21.0122,
        ],
        [
            'city' => 'Budapest',
            'country' => 'Hungary',
            'latitude' => 47.4979,
            'longitude' => 19.0402,
        ],
        [
            'city' => 'Bucharest',
            'country' => 'Romania',
            'latitude' => 44.4268,
            'longitude' => 26.1025,
        ],
        [
            'city' => 'Athens',
            'country' => 'Greece',
            'latitude' => 37.9755,
            'longitude' => 23.7348,
        ],
        [
            'city' => 'Helsinki',
            'country' => 'Finland',
            'latitude' => 60.1699,
            'longitude' => 24.9384,
        ],
        [
            'city' => 'Stockholm',
            'country' => 'Sweden',
            'latitude' => 59.3293,
            'longitude' => 18.0686,
        ],
        [
            'city' => 'Oslo',
            'country' => 'Norway',
            'latitude' => 59.9139,
            'longitude' => 10.7522,
        ],
        [
            'city' => 'Copenhagen',
            'country' => 'Denmark',
            'latitude' => 55.6761,
            'longitude' => 12.5683,
        ],
        [
            'city' => 'Dublin',
            'country' => 'Ireland',
            'latitude' => 53.3498,
            'longitude' => -6.2603,
        ],
        [
            'city' => 'Edinburgh',
            'country' => 'United Kingdom',
            'latitude' => 55.9533,
            'longitude' => -3.1883,
        ],
        [
            'city' => 'Montreal',
            'country' => 'Canada',
            'latitude' => 45.5017,
            'longitude' => -73.5673,
        ]];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $prefix = Config::string('stickle.database.tablePrefix');

        DB::table("{$prefix}location_data")->truncate();

        foreach (self::CITIES as $city) {
            LocationData::query()->create([
                'ip_address' => fake()->ipv4(),
                'city' => $city['city'],
                'country' => $city['country'],
                'coordinates' => DB::raw("ST_SetSRID(ST_MakePoint({$city['longitude']}, {$city['latitude']}), 4326)"),
            ]);
        }
    }
}
