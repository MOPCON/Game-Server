<?php

use App\Mission;
use App\MissionFlow;
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
        $fake_data_total = 12;

        while ($item_count < $fake_data_total) {
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

            $task = Task::create($task_data);

            if ($item_count + 1 < $fake_data_total) {
                $flow_data = [
                    'mission_id' => $mission->id,
                    'task_id' => $task->id,
                    'next_mission_id' => $item_count + 2.
                ];

                MissionFlow::create($flow_data);
            }

            $item_count++;
        }
    }
}
