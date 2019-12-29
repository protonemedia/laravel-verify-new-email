<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Http\InvalidVerificationLinkException;
use ProtoneMedia\LaravelVerifyNewEmail\Http\VerifyNewEmailController;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyNewEmailControllerTest extends TestCase
{
    /** @test */
    public function it_updates_the_user_email_and_deletes_the_pending_email()
    {
        Mail::fake();

        $user = $this->user();

        $pendingUserEmail = $user->newEmail('new@example.com');

        app(VerifyNewEmailController::class)->verify($pendingUserEmail->token);

        $user = $user->fresh();

        $this->assertEquals('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($pendingUserEmail->fresh());
    }

    /** @test */
    public function it_removes_both_pending_models_if_two_users_try_to_verify_the_same_address()
    {
        Mail::fake();

        $userA = $this->user();
        $userB = $this->user('another@example.com');

        $pendingUserEmailA = $userA->newEmail('new@example.com');
        $userB->newEmail('new@example.com');

        app(VerifyNewEmailController::class)->verify($pendingUserEmailA->token);

        $this->assertEmpty(PendingUserEmail::get());
    }

    /** @test */
    public function it_throws_an_exception_if_the_token_is_invalid()
    {
        try {
            app(VerifyNewEmailController::class)->verify('wrong_token');
        } catch (InvalidVerificationLinkException $exception) {
            return    $this->assertTrue(true);
        }

        $this->fail('Should have thrown InvalidVerificationLinkException');
    }
}
