<?php

namespace App\Exceptions;

use App\Mail\ExceptionOccured;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            if (config('app.env') == 'demo') {
                $this->sendEmail($exception); // sends an email in demo server
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Sends the exception email
     *
     * @param $exception
     */
    public function sendEmail(Exception $exception)
    {
        try {
            $e = FlattenException::create($exception);

            $handler = new SymfonyExceptionHandler();

            $html = $handler->getHtml($e);
            $email = env('MAIL_USERNAME');

            if (! empty($email)) {
                Mail::to($email)->send(new ExceptionOccured($html));
            }
        } catch (Exception $ex) {
            dd($ex);
        }
    }
}
