<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

namespace Lcharette\AuthApi\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\User;
use Illuminate\Validation\ValidationException;

/**
 * Handles the first part of the password recovery process,
 * collecting an email address and generating a password recovery link.
 *
 * @see https://github.com/laravel/ui/blob/2.x/auth-backend/SendsPasswordResetEmails.php
 * @see https://github.com/laravel/ui/blob/2.x/auth-backend/ResetsPasswords.php
 */
class ResetPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->emailCredentials($request)
        );

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetResponse($response)
                    : $this->sendResetFailedResponse($response);
    }

    /**
     * Reset the given user's password.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate($this->resetRules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->emailCredentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Validate the email for the given request.
     *
     * @param Request $request
     */
    protected function validateEmail(Request $request): void
    {
        $request->validate(['email' => 'required|email']);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return string[]
     */
    protected function resetRules(): array
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return string[]
     */
    protected function validationErrorMessages(): array
    {
        return [];
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param Request $request
     *
     * @return string[]
     */
    protected function emailCredentials(Request $request): array
    {
        return $request->only('email');
    }

    /**
     * Reset the given user's password.
     *
     * @param User   $user
     * @param string $password
     */
    protected function resetPassword(User $user, string $password): void
    {
        $this->setUserPassword($user, $password);
        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    /**
     * Set the user's password.
     *
     * @param User   $user
     * @param string $password
     */
    protected function setUserPassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param Request $request
     *
     * @return string[]
     */
    protected function credentials(Request $request): array
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param string $response
     *
     * @return JsonResponse
     */
    protected function sendResetResponse(string $response): JsonResponse
    {
        return new JsonResponse(['message' => trans($response)], 200);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param string $response
     *
     * @throws ValidationException
     */
    protected function sendResetFailedResponse(string $response): void
    {
        throw ValidationException::withMessages([
            'email' => [trans($response)],
        ]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker();
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return StatefulGuard
     */
    protected function guard(): StatefulGuard
    {
        return Auth::guard();
    }
}
