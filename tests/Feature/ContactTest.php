<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/contacts', [
            'first_name' => 'Oriz',
            'last_name' => 'Wahyu N',
            'email' => 'admin@orzproject.dev',
            'phone' => '081228390894'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'first_name' => 'Oriz',
                    'last_name' => 'Wahyu N',
                    'email' => 'admin@orzproject.dev',
                    'phone' => '081228390894'
                ]
            ]);
    }

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/contacts', [
            'first_name' => '',
            'last_name' => 'Wahyu N',
            'email' => 'admin',
            'phone' => '081228390894'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ],
                    'email' => [
                        'The email field must be a valid email address.'
                    ]
                ]
            ]);
    }

    public function testCreateUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/contacts', [
            'first_name' => '',
            'last_name' => 'Wahyu N',
            'email' => 'admin',
            'phone' => '081228390894'
        ], [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => 'test',
                    'last_name' => 'test',
                    'email' => 'test@test.test',
                    'phone' => '085758'
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Contact not found'
                    ]
                ]
            ]);
    }

    public function testGetOtherUserContact()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Contact not found'
                    ]
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->put(
            '/api/contacts/' . $contact->id,
            [
                'first_name' => 'test2',
                'last_name' => 'test2',
                'email' => 'test2@test.test',
                'phone' => '0857582'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => 'test2',
                    'last_name' => 'test2',
                    'email' => 'test2@test.test',
                    'phone' => '0857582'
                ]
            ]);
    }

    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->put(
            '/api/contacts/' . $contact->id,
            [
                'first_name' => '',
                'last_name' => 'test2',
                'email' => 'test2@test.test',
                'phone' => '0857582'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ]
                ]
            ]);
    }
}
