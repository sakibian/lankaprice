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
            'Laravel', 'PHP', 'JavaScript', 'React', 'Vue.js',
            'Tailwind CSS', 'Bootstrap', 'MySQL', 'MongoDB', 'Node.js',
            'API', 'RESTful', 'GraphQL', 'Web Development', 'Frontend',
            'Backend', 'Full-Stack', 'DevOps', 'Cloud Computing', 'Cybersecurity'
        ];


        foreach ($tags as $tag) {
            Tag::updateOrCreate(['name' => $tag]);
        }
    }
}
