<?php

use App\Mission;
use App\Task;
use Illuminate\Database\Seeder;

class MissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('zh_TW');
        $en_faker = Faker\Factory::create('en_US');
        $item_count = 0;

        while ($item_count < 12) {
            $data = [
                'name' => sprintf("關卡 %s", $item_count + 1),
                'name_e' => sprintf("Mission %s", $item_count + 1),
                'description' => $faker->realtext(20),
                'description_e' => $en_faker->text,
                'open' => 1,
            ];

            $mission = Mission::create($data);

            $task_data = [
                'name' => sprintf("任務 %s", $item_count + 1),
                'name_e' => sprintf("Task %s", $item_count + 1),
                'description' => $faker->realtext(20),
                'description_e' => $en_faker->text,
                'image' => $faker->imageUrl('640', '480', 'technics', true, 'Faker'),
                'mission_uid' => $mission->uid,
            ];
            Task::create($task_data);
            $item_count++;
        }
    }
}
