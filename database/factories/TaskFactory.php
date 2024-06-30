<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id'=> User::all()->random()->id,
            'title'=> $this->faker->sentence(2),
            'description'=>$this->faker->sentence(),
            'category_id'=> Category::all()->random()->id,
            'due_date'=>$this->faker->date('Y-m-d')
        ];
    }
}
