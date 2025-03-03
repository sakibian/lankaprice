<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $tags = [
            'Cars', 'Van', 'Motorcycle', 'Three Wheel', 'SUV / JEEP',
            'Bus', 'Lorry / Tipper', 'Bicycle', 'Tractor', 'Crew Cab', 'Pick up / Double Cab', 
            'Heavy Duty', 'Boats & Water Transport', 'Auto Services',
            'Auto Parts', 'Maintenance and Repair', 'Rentals', 'General', 'Other'
        ];


        foreach ($tags as $tag) {
            Tag::updateOrCreate(['name' => $tag]);
        }
    }
}
