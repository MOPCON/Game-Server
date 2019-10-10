<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use App\User;
use App\Mission;
use App\KeyPool;

class AuthControllerTest extends TestCase
{
    public function testRegister()
    {
        $faker = \Faker\Factory::create();

        $response = $this->json('POST', '/register', [
            "uid" => $faker->uuid,
            "email" => $faker->email,
        ]);

        $response->seeJson([
            "token_type" => "bearer",
        ]);
    }

    public function testLogin()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', '/login', [
            "uid" => $user->uid,
            "password" => $user->email,
        ]);

        $response->seeJson([
            "token_type" => "bearer",
        ]);
    }

    public function testGetIntro()
    {
        $response = $this->json('GET', '/intro');

        $response->seeJson([
            'success' => true,
            "message" => "Success.",
        ]);
    }

    public function testGetMe()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('GET', '/me');

        $response->seeJson([
            'success' => true,
            "message" => "Success.",
        ]);
    }

    public function testGetTask()
    {
        $faker = \Faker\Factory::create();
        $mission = Mission::create([
            'name' => $faker->name,
            'name_e' => $faker->name,
        ]);

        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('GET', '/getTask/' . $mission->uid);

        $response->seeJson([
            'success' => true,
            "message" => "Success.",
        ]);
    }

    public function testGetReward()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('GET', '/getReward');

        $response->seeJson([
            'success' => true,
            "message" => "Success.",
        ]);
    }

    public function testInvite()
    {
        $faker = \Faker\Factory::create();

        $response = $this->json('POST', '/register', [
            "uid" => $faker->uuid,
            "email" => $faker->email,
        ]);

        $response = $this->json('POST', '/invite', [
            "uid" => $faker->uuid,
            "email" => 'test@te.st',
        ]);

        $response->seeJson([
            "token_type" => "bearer",
        ]);
    }
}
