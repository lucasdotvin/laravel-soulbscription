<?php

namespace LucasDotDev\Soulbscription\Tests\Mocks\Database\Factories;

use LucasDotDev\Soulbscription\Tests\Mocks\Models\User;
use Orchestra\Testbench\Factories\UserFactory as OrchestraUserFactory;

class UserFactory extends OrchestraUserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
}
