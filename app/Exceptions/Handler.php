<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * No bare Laravel/Symfony error page for normal browser navigation, ever — every error
     * (abort_if messages, 404s, CSRF/419, and genuine 500s) bounces the user back to where
     * they came from with a message flashed, so the layout shows it as a popup instead.
     *
     * ValidationException is left to the default handling (inline field errors already read
     * better there) and AuthenticationException is left alone so the auth guard's normal
     * "redirect to login" behavior keeps working.
     */
    public function render($request, Throwable $e)
    {
        if (! $request->expectsJson()
            && ! $e instanceof ValidationException
            && ! $e instanceof AuthenticationException
        ) {
            $previousUrl = $request->hasSession() ? $request->session()->get('_previous.url') : null;

            $redirectTo = ($previousUrl && $previousUrl !== $request->fullUrl())
                ? redirect($previousUrl)
                : redirect()->route('admin.home');

            return $redirectTo->with('popup_error', $this->popupMessage($e));
        }

        return parent::render($request, $e);
    }

    private function popupMessage(Throwable $e): string
    {
        if ($e instanceof HttpException) {
            $message = trim((string) $e->getMessage());

            if ($message !== '') {
                return $message;
            }

            return match ($e->getStatusCode()) {
                404 => 'The page you are looking for was not found.',
                403 => 'You are not allowed to perform this action.',
                419 => 'Your session has expired. Please refresh and try again.',
                405 => 'This action is not supported.',
                default => 'Something went wrong. Please try again.',
            };
        }

        return 'Something went wrong. Please try again.';
    }
}
