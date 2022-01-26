<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\InvalidEmailVerificationModelException;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\NotifyOldEmail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\RecoverEmail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyFirstEmail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class MustVerifyNewEmailTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_the_model_class_configuration_is_not_set()
    {
        $user = $this->user();

        config(['verify-new-email.model' => null]);

        try {
            $user->getEmailVerificationModel();
        } catch (InvalidEmailVerificationModelException $e) {
            return $this->assertTrue(true);
        }

        $this->fail('Should have thrown InvalidEmailVerificationModelException');
    }

    /** @test */
    public function it_doesnt_send_a_verification_mail_if_the_email_didnt_change()
    {
        Mail::fake();

        $user = $this->user();

        $user->email_verified_at = now();
        $user->save();

        $this->assertNull($user->newEmail($user->email));

        Mail::assertNothingQueued();
    }

    /** @test */
    public function it_uses_another_mailable_for_updating_an_email_address()
    {
        Mail::fake();

        $user = $this->user();

        $user->email_verified_at = now();
        $user->save();

        $pendingUserEmail = $user->newEmail('new@example.com');

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
    public function it_can_generate_a_token_and_mail_it_to_the_new_email_address()
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

        Mail::assertQueued(VerifyFirstEmail::class, function (Mailable $mailable) use ($pendingUserEmail) {
            $this->assertTrue($mailable->pendingUserEmail->is($pendingUserEmail));

            $this->assertTrue($mailable->hasTo('new@example.com'));

            $this->assertFalse($mailable->hasTo('old@example.com'));
            $this->assertFalse($mailable->hasCc('old@example.com'));
            $this->assertFalse($mailable->hasBcc('old@example.com'));

            return true;
        });

        $this->assertEquals('new@example.com', $user->getPendingEmail());
    }

    /** @test */
    public function it_can_regenerate_a_token_and_mail_it()
    {
        Mail::fake();

        $user = $this->user();

        $pendingUserEmailFirst = $user->newEmail('new@example.com');

        // reset mail fake
        Mail::fake();
        Mail::assertNothingQueued();

        $pendingUserEmailSecond = $user->resendPendingEmailVerificationMail();

        // verify it deleted the first one
        $this->assertNull($pendingUserEmailFirst->fresh());

        // verify it generated a new token
        $this->assertNotEquals($pendingUserEmailFirst->token, $pendingUserEmailSecond->token);

        Mail::assertQueued(VerifyFirstEmail::class, function (Mailable $mailable) {
            $this->assertTrue($mailable->hasTo('new@example.com'));

            $this->assertFalse($mailable->hasTo('old@example.com'));
            $this->assertFalse($mailable->hasCc('old@example.com'));
            $this->assertFalse($mailable->hasBcc('old@example.com'));

            return true;
        });
    }

    /** @test */
    public function it_deletes_previous_attempts_of_the_user_trying_to_verify_a_new_email()
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

    /** @test */
    public function it_sends_a_recovery_email_before_verification()
    {
        config(['verify-new-email.send_recovery_email' => 'before_verification']);

        Mail::fake();
        
        $user = $this->user();
        
        $user->email_verified_at = now();
        $user->save();
        
        $user->newEmail('new@example.com');

        $this->assertDatabaseHas('pending_user_emails', [
            'email' => $user->email,
            'type'  => PendingUserEmail::TYPE_RECOVER
        ]);
            
        Mail::assertQueued(RecoverEmail::class);
    }

    /** @test */
    public function it_sends_a_recovery_email_after_verification()
    {
        config(['verify-new-email.send_recovery_email' => 'after_verification']);
    
        Mail::fake();
            
        $user = $this->user();
            
        $user->email_verified_at = now();
        $user->save();
            
        $model = $user->newEmail('new@example.com');

        $model->activate();

        $this->assertDatabaseHas('pending_user_emails', [
            'email' => $user->email,
            'type'  => PendingUserEmail::TYPE_RECOVER
        ]);
                
        Mail::assertQueued(RecoverEmail::class);
    }

    /** @test */
    public function it_does_not_send_a_recovery_email_when_disabled_in_config()
    {
        config(['verify-new-email.send_recovery_email' => false]);
        
        Mail::fake();
                
        $user = $this->user();
                
        $user->email_verified_at = now();
        $user->save();
                
        $model = $user->newEmail('new@example.com');
    
        $model->activate();
                    
        Mail::assertNotQueued(RecoverEmail::class);
    }
}
