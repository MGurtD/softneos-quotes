<?php

namespace Tests\Feature;

use App\Models\SuiteModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_corporate_page_lists_suite_modules_from_the_database(): void
    {
        SuiteModule::factory()->create(['name' => 'Producció', 'position' => 2]);
        SuiteModule::factory()->markedAsNew()->create(['name' => 'Pressupostos', 'position' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Pressupostos', 'Nou', 'Producció']);
    }
}
