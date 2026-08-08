<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
     * abort_if($cond, $code, 'Some human message') calls across the admin controllers throw a
     * plain HttpException. Left alone, Laravel renders those as a bare framework error page.
     * For normal browser navigation we instead bounce the user back to where they came from
     * with the message flashed, so the layout can show it as a popup instead of a dead page.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof HttpException
            && ! $request->expectsJson()
            && $e->getStatusCode() !== 404
            && trim((string) $e->getMessage()) !== ''
        ) {
            $previousUrl = $request->hasSession() ? $request->session()->get('_previous.url') : null;

            $redirectTo = ($previousUrl && $previousUrl !== $request->fullUrl())
                ? redirect($previousUrl)
                : redirect()->route('admin.home');

            return $redirectTo->with('popup_error', $e->getMessage());
        }

        return parent::render($request, $e);
    }
}
