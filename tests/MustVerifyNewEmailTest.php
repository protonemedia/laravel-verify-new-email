<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingEmailAddress;

class MustVerifyNewEmailTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_token_and_mail_it_to_the_new_email_address():void
    {
        Mail::fake();

        $user = $this->user();

        $pendingEmailAddress = $user->newEmail('new@example.com');

        $this->assertInstanceOf(PendingEmailAddress::class, $pendingEmailAddress);
        $this->assertEquals('old@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('pending_email_addresses', [
            'user_type' => User::class,
            'user_id'   => $user->id,
            'email'     => 'new@example.com',
        ]);

        $token = $pendingEmailAddress->token;

        Mail::assertQueued(VerifyNewEmail::class, function (Mailable $mailable) use ($token) {
            $this->assertTrue(Hash::check($mailable->token, $token));

            $this->assertTrue($mailable->hasTo('new@example.com'));

            $this->assertFalse($mailable->hasTo('old@example.com'));
            $this->assertFalse($mailable->hasCc('old@example.com'));
            $this->assertFalse($mailable->hasBcc('old@example.com'));

            return true;
        });
    }
}
