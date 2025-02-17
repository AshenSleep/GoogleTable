<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Record;
use Illuminate\Database\Seeder;

class RecordSeeder extends Seeder
{
    public function run()
    {
        Record::factory()->count(1000)->create();
    }
}
