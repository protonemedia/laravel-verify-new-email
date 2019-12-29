<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class MustVerifyNewEmailTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_token_and_mail_it_to_the_new_email_address():void
    {
        Mail::fake();

        $user = $this->user();

        $pendingUserEmail = $user->newEmail('new@example.com');

        $this->assertInstanceOf(PendingUserEmail::class, $pendingUserEmail);
        $this->assertEquals('old@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('pending_user_emails', [
            'user_type' => User::class,
            'user_id'   => $user->id,
            'email'     => 'new@example.com',
        ]);

        Mail::assertQueued(VerifyNewEmail::class, function (Mailable $mailable) use ($pendingUserEmail) {
            $this->assertTrue($mailable->pendingUserEmail->is($pendingUserEmail));

            $this->assertTrue($mailable->hasTo('new@example.com'));

            $this->assertFalse($mailable->hasTo('old@example.com'));
            $this->assertFalse($mailable->hasCc('old@example.com'));
            $this->assertFalse($mailable->hasBcc('old@example.com'));

            return true;
        });
    }
    /** @test */
    public function it_can_regenerate_a_token_and_mail_it():void
    {
        Mail::fake();

        $user = $this->user();

        $pendingUserEmailFirst = $user->newEmail('new@example.com');

        // reset mail fake
        Mail::fake();
        Mail::assertNothingQueued();

        $pendingUserEmailSecond = $user->resendPendingUserEmailVerificationMail();

        // verify it deleted the first one
        $this->assertNull($pendingUserEmailFirst->fresh());

        // verify it generated a new token
        $this->assertNotEquals($pendingUserEmailFirst->token, $pendingUserEmailSecond->token);

        Mail::assertQueued(VerifyNewEmail::class, function (Mailable $mailable) {
            $this->assertTrue($mailable->hasTo('new@example.com'));

            $this->assertFalse($mailable->hasTo('old@example.com'));
            $this->assertFalse($mailable->hasCc('old@example.com'));
            $this->assertFalse($mailable->hasBcc('old@example.com'));

            return true;
        });
    }

    /** @test */
    public function it_deletes_previous_attempts_of_the_user_trying_to_verify_a_new_email():void
    {
        Mail::fake();

        $user = $this->user();

        $user->newEmail('new@example.com');
        $this->assertCount(1, PendingUserEmail::get());

        $user->newEmail('another@example.com');
        $this->assertCount(1, PendingUserEmail::get());

        $this->assertDatabaseMissing('pending_user_emails', [
            'email' => 'new@example.com',
        ]);
    }
}
