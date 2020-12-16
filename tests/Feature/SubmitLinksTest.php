<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitLinksTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function linkIsNotCreatedIfValidationFails() {
        $response = $this->post('/submitnew');

        $response->assertSessionHasErrors(['title', 'url', 'description']);
    }


    /**
     * @test
     */
    public function linkIsNotCreatedWithAnInvalidUrl() {
        $this->withoutExceptionHandling();

        $cases = ['//invalid-url.com', '/invalid-url', 'foo.com'];

        foreach ($cases as $case) {
            try {
                $response = $this->post('/submitnew', [
                    'title' => 'Example Title',
                    'url' => $case,
                    'description' => 'Example description'
                ]);
            }
            catch (ValidationException $e) {
                $this->assertEquals('The url format is invalid.',
                $e->validator->errors()->first('url'));

                continue;
            }

            $this->fail("The URL $case passed validation when it should have failed.");
        }
    }


    /**
     * @test
     */
    public function maxLengthFailsWhenTooLong() {

        $this->withoutExceptionHandling();

        $title = str_repeat('a', 256);
        $description = str_repeat('a', 256);
        $url = 'http://';
        $url .= str_repeat('a', 256 - strlen($url));

        try {
            $this->post('/submitnew', compact('title', 'url', 'description'));
        }
        catch (ValidationException $e) {

            $this->assertEquals(
                'The title may not be greater than 255 characters.',
                $e->validator->errors()->first('title')
            );

            $this->assertEquals(
                'The url may not be greater than 255 characters.',
                $e->validator->errors()->first('url')
            );

            $this->assertEquals(
                'The description may not be greater than 255 characters.',
                $e->validator->errors()->first('description')
            );

            return;
        }

        $this->fail('Max length should trigger a ValidationException');
    }


    /**
     * @test
     */
    public function maxLengthSucceedsWhenUnderMax() {

        $url = 'http://';
        $url .= str_repeat('a', 255 - strlen($url));

        $data = [
            'title' => str_repeat('a', 255),
            'url' => $url,
            'description' => str_repeat('a', 255)
        ];

        $this->post('/submitnew', $data);

        $this->assertDatabaseHas('links', $data);
    }


    /**
     * @test
     */
    public function guestCanSubmitANewLink() {
        $response = $this->post('/submitnew', [
            'title' => 'Example Title',
            'url' => 'http://example.com',
            'description' => 'Example description.'
        ]);

        $this->assertDatabaseHas('links', ['title' => 'Example Title']);

        $response->assertStatus(302)->assertHeader('Location', url('/'));

        $this->get('/')->assertSee('Example Title');
    }
}
