<?php

namespace KolaKachi\Bacs\Tests;

use Tests\TestCase;

class BacsControllerTest extends TestCase
{
    /** @test */
    public function it_returns_a_valid_bacs_response()
    {
        $response = $this->get('api/bacs-response');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'vol',
                    'hdr1',
                    'hdr2',
                    'uhl',
                    'standard',
                    'eof1',
                    'eof2',
                    'utl',
                ],
            ]);
    }
}
