<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        DB::table('categories')->insert([
//            'user_id' => User::all()->random()->id,
//            'title'=>Str::random(10),
//            'description'=>Str::random(10)
//        ]);
        // Using the factory to create 20 categories
        Category::factory()->count(10)->create()->each(function ($category) {
            $category->tasks()->saveMany(Task::factory()->count(10)->create());
        });

    }


}
