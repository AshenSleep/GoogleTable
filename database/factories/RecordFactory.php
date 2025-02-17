<?php

namespace Database\Factories;

use App\Models\Record;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordFactory extends Factory
{
    protected $model = Record::class;

    public function definition()
    {
        return [
            'content' => $this->faker->text(50),
            'status' => $this->faker->randomElement(['Allowed', 'Prohibited']),
        ];
    }
}
