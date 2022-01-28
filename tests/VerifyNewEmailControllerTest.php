<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Http\InvalidVerificationLinkException;
use ProtoneMedia\LaravelVerifyNewEmail\Http\VerifyNewEmailController;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyNewEmailControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['verify-new-email.login_after_verification' => false]);
    }

    /** @test */
    public function it_updates_the_user_email_and_deletes_the_pending_email()
    {
        Event::fake();
        Mail::fake();

        $user = $this->user();

        $pendingUserEmail = $user->newEmail('new@example.com');

        $redirect = app(VerifyNewEmailController::class)->verify($pendingUserEmail->token);

        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertEquals('http://localhost/home', $redirect->getTargetUrl());

        $user = $user->fresh();

        $this->assertEquals('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($pendingUserEmail->fresh());
        $this->assertNull($user->getPendingEmail());

        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->is($user);
        });
    }

    /** @test */
    public function it_can_login_the_user()
    {
        Mail::fake();

        $user = $this->user();

        $pendingUserEmail = $user->newEmail('new@example.com');

        config(['verify-new-email.login_after_verification' => true]);

        $response = app(VerifyNewEmailController::class)->verify($pendingUserEmail->token);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertAuthenticatedAs($user);
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

    /** @test */
    public function it_can_recover_an_account()
    {
        config(['verify-new-email.send_recovery_email' => 'before_verification']);

        Mail::fake();

        $user = $this->user();

        $user->newEmail('new@example.com');

        $pendingUserEmail = $user->newRecovery('old@example.com');

        $this->assertNotEmpty(PendingUserEmail::where('type', 'recover')->get());

        app(VerifyNewEmailController::class)->verify($pendingUserEmail->token);

        $this->assertEmpty(PendingUserEmail::where('type', 'recover')->get());
        $this->assertEquals($user->fresh()->email, 'old@example.com');
    }

    /** @test */
    public function it_can_recover_an_account_after_email_has_been_changed()
    {
        config(['verify-new-email.send_recovery_email' => 'before_verification']);
    
        Mail::fake();
    
        $user = $this->user();
    
        $pendingUserEmail = $user->newEmail('new@example.com');
        
        app(VerifyNewEmailController::class)->verify($pendingUserEmail->token);
        
        // Assert email has been changed
        $this->assertEquals($user->fresh()->email, 'new@example.com');
        
        $recoverUserEmail = $user->newRecovery('old@example.com');
        
        // Assert account email can be recovered.
        $this->assertNotEmpty(PendingUserEmail::where('type', 'recover')->get());
        
        // Recover account.
        app(VerifyNewEmailController::class)->verify($recoverUserEmail->token);

        $this->assertEmpty(PendingUserEmail::where('type', 'recover')->get());
        $this->assertEquals($user->fresh()->email, 'old@example.com');
    }
}
