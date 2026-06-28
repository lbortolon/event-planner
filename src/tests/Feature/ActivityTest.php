<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

use App\Models\Activity;
use Illuminate\Testing\Fluent\AssertableJson;

class ActivityTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */

    // controllo il funzionamento dell'endpoint che mostra le attività
    public function test_returns_successful_activities_response(): void
    {
        $user = User::factory()->create();
        $userActivity = Activity::factory()->create([
            "user_id"=> $user->id,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/activities');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', 1)
                    ->has('data.0', fn (AssertableJson $json) =>
                        $json
                        ->where('id', $userActivity->id)
                        ->where('user_id', $userActivity->user_id)
                        ->where('title', $userActivity->title)
                        ->where('notes', $userActivity->notes)
                        ->where('location', $userActivity->location)
                        ->has('role')
                        ->has('invitations')
                        ->etc()
                    )
            );
    }

    // controllo il funzionamento dell'endpoint che crea un'attività, con dati in input validi
    public function test_create_activity_valid_input() : void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $title = fake()->word();
        $location = fake()->address();
        // data successiva a oggi
        $starts_at = fake()->dateTimeBetween('+0 days', '+2 years');
        $starts_at_input = $starts_at->format('Y-m-d H:i:s');
        $starts_at_expected = $starts_at->format('Y-m-d\TH:i:s.000000\Z');
        $notes = fake()->sentence(5);

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/activities', [
                            "title" => $title,
                            "location" => $location,
                            "starts_at" => $starts_at_input,
                            "notes" => $notes,
                        ]);

        $response->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->where('data.title', $title)
                    ->where('data.location', $location)
                    ->where('data.starts_at', $starts_at_expected)
                    ->where('data.notes', $notes)
                    ->etc()
            );
    }

    // controllo il funzionamento dell'endpoint che crea un'attività, con dati in input NON validi
    public function test_create_activity_invalid_input() : void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $title = fake()->word();
        $location = fake()->address();
        // data precedente a oggi
        $starts_at = fake()->dateTimeBetween('-2 years', '-2 day');
        $starts_at_input = $starts_at->format('Y-m-d H:i:s');
        $starts_at_expected = $starts_at->format('Y-m-d\TH:i:s.000000\Z');
        $notes = fake()->sentence(5);

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/activities', [
                            "title" => $title,
                            "location" => $location,
                            "starts_at" => $starts_at_input,
                            "notes" => $notes,
                        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['starts_at']);
    }

    // controllo il funzionamento dell'endpoint che aggiorne un'attività, con dati in input validi
    public function test_update_activity_valid_input() : void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $userActivity = Activity::factory()->create([
            'user_id'=> $user->id,
        ]);

        $title = fake()->word();
        $location = fake()->address();
        // data successiva a oggi
        $starts_at = fake()->dateTimeBetween('+0 days', '+2 years');
        $starts_at_input = $starts_at->format('Y-m-d H:i:s');
        $starts_at_expected = $starts_at->format('Y-m-d\TH:i:s.000000\Z');
        $notes = fake()->sentence(5);

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->putJson('/api/activities/' . $userActivity->id, [
                            "title" => $title,
                            "location" => $location,
                            "starts_at" => $starts_at_input,
                            "notes" => $notes,
                        ]);
                        
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->where('data.title', $title)
                    ->where('data.location', $location)
                    ->where('data.starts_at', $starts_at_expected)
                    ->where('data.notes', $notes)
                    ->etc()
            );  
        
    }

    // controllo il funzionamento dell'endpoint che aggiorne un'attività, con dati in input NON validi
    public function test_update_activity_invalid_input() : void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $userActivity = Activity::factory()->create([
            'user_id'=> $user->id,
        ]);

        $title = fake()->word();
        $location = fake()->address();
        // data precedente a oggi
        $starts_at = fake()->dateTimeBetween('-2 years', '-2 day');
        $starts_at_input = $starts_at->format('Y-m-d H:i:s');
        $notes = fake()->sentence(5);

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->putJson('/api/activities/' . $userActivity->id, [
                            "title" => $title,
                            "location" => $location,
                            "starts_at" => $starts_at_input,
                            "notes" => $notes,
                        ]);
                        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['starts_at']);
        
    }
    // controllo il funzionamento dell'endpoint che aggiorna un'attività, con utente NON organizzatore
    public function test_update_activity_forbidden_by_non_organizer(): void
    {
        $organizer = User::factory()->create();
        $otherUser = User::factory()->create();
        $activity = Activity::factory()->create(['user_id' => $organizer->id]);
        $token = $otherUser->createToken('test')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/activities/' . $activity->id, [
                'title' => 'Titolo modificato',
                'starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(403);
    }

    // controllo il funzionamento dell'endpoint che elimina un'attività, con utente organizzatore
    public function test_delete_activity() : void
    {
        $user = User::factory()->create();
        $userActivity = Activity::factory()->create([
            "user_id"=> $user->id,
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->deleteJson('/api/activities/' . $userActivity->id);

        $this->assertSoftDeleted('activities', ['id' => $userActivity->id]);

        $response->assertStatus(200)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', 'Attività eliminata.')
                );
    }
    // controllo il funzionamento dell'endpoint che elimina un'attività, con utente NON organizzatore
    public function test_delete_activity_forbidden_by_non_organizer(): void
    {
        $organizer = User::factory()->create();
        $otherUser = User::factory()->create();
        $activity = Activity::factory()->create(['user_id' => $organizer->id]);
        $token = $otherUser->createToken('test')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/activities/' . $activity->id);

        $response->assertStatus(403);
    }
}