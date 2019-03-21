<?php

namespace Betalectic\Ocupado\Test;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Betalectic\Ocupado\Models\Entity;
use Betalectic\Ocupado\Ocupado;
use App\User;

class FirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_register_entity()
    {
        $user = User::create([
            'name' => 'Rajiv',
            'email' => 'rajiv@betlaectic.com',
            'password' => 'password'
        ]);

        $ocupado = new Ocupado();
        $ocupado->registerEntity($user);

        $this->assertDatabaseHas('ocupado_entities', [
            'type' => get_class($user),
            'value' => $user->getKey()
        ]);
    }
}
