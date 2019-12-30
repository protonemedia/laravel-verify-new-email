<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Support\Str;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyNewEmailTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_signed_url()
    {
        $mailable = new VerifyNewEmail(
            new PendingUserEmail(['token' => 'random_token'])
        );

        $this->assertNotEmpty($mailable->render());

        $url = $mailable->build()->viewData['url'];

        $this->assertTrue(Str::startsWith($url, 'http://localhost/pendingEmail/verify/random_token?expires='));

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertArrayHasKey('signature', $query);
    }
}
