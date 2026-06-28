<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // controllo il funzionamento dell'endpoint di login con utente e password corrette
    public function test_login_correct_user_and_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        // Mi aspetto stato 200
        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email']
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json->missing('user.password')
                    ->where('user.email', $user->email)
                    ->where('user.name', $user->name)
                    ->where('user.id', $user->id)
                    ->etc()
            );
    }

    // controllo il funzionamento dell'endpoint di login con utente e password non corrette
    public function test_login_wrong_credentials(): void
    {
        // creo un utente con mail e password noti
        User::factory()->create([
            'email' => 'correct@email.com',
            'password' => bcrypt('password')
        ]);

        // immetto utente e password diversi
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrong_password'
        ]);

        //l'errore viene dato sempre sulla mail    
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // controllo il funzionamento dell'endpoint di login con utente invalido (username deve essere indirizzo email)
    public function test_login_invalid_username(): void
    {
        User::factory()->create([
            'password' => bcrypt('password')
        ]);

        // la password è corretta, solo username invalido
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password'
        ]);

        // l'errore viene dato sempre sulla mail 
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // controllo il funzionamento dell'endpoint di registrazione con input valido
    public function test_registration_valid_input(): void
    {
        $userTestName = 'Test User';
        $userTestEmail = 'test@user.com';
        $userTestPassword = 'Password';

        // input valido
        $response = $this->postJson('/api/register', [
            'name' => $userTestName,
            'email' => $userTestEmail,
            'password' => $userTestPassword,
            'password_confirmation' => $userTestPassword,
        ]);

        // risposta con stato 201, determinata struttura e valori, niente password
        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email']
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('user.name', $userTestName)
                ->where('user.email', $userTestEmail)
                ->missing('user.password')
                ->etc()
            );
    }

    // controllo il funzionamento dell'endpoint di registrazione con utente invalido (username deve essere indirizzo email, nome non deve essere vuoto)
    public function test_registration_invalid_input(): void
    {
        // input invalido
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // risposta con stato 422, errore di validazione su nome e email, niente dati
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email'])
            ->assertJson(fn (AssertableJson $json) =>
                $json->missing('data')
                ->etc()
            );
    }

    // controllo il funzionamento dell'endpoint di registrazione con utente esistente
    public function test_registration_existing_user(): void
    {
        $user = User::factory()->create([
            'email'=> 'test@user.com',
        ]);

        // utente esistente
        $response = $this->postJson('/api/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // risposta con stato 422, errore di validazione su email, niente dati, messaggio di errore stabilito
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson(fn (AssertableJson $json) =>
                $json->missing('data')
                ->where('message', 'The email has already been taken.')
                ->etc()
            );
    }

    // controllo il funzionamento dell'endpoint che mostra sè stessi
    public function test_returns_successful_me_response(): void
    {
        //creo utente e token
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Richiesta con token
        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/user');

        // risposta con stato 200, utente con dati corretti e niente password
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                    $json->where('data.id', $user->id)
                        ->where('data.name', $user->name)
                        ->where('data.email', $user->email)
                        ->missing('data.password')
                );
    }

    // controllo il funzionamento dell'endpoint di logout
    public function test_logout(): void
    {
        //creo utente e token
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Richiesta con token
        $response = $this
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/logout');
        
        // risposta con stato 200, messaggio corretto e niente dati
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', 'Logout effettuato con successo.')
                        ->missing('data')
                );

        // dopo il logout
        auth()->forgetGuards();

        // Seconda chiamata con lo stesso token
        // dopo il logout, lo stesso token non deve più funzionare
        $responseAfterLogout = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $responseAfterLogout->assertStatus(401);


    }
    
}