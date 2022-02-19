<?php

namespace App\Exceptions;

use App\Enums\ResponseCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            $response = array_merge([
                'code' => ResponseCode::INVALID_DATA,
                'message' => $exception->getMessage(),
            ], $exception->errors());
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'code' => ResponseCode::NO_DATA_FOUND,
                'message' => __('response.not_found'),
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof BiggerThanTicketLimitException) {
            return response()->json([
                'code' => ResponseCode::RESERVATIONS_BIGGER_THAN_ALLOWED,
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof NoAvailableTicketException) {
            return response()->json([
                'code' => ResponseCode::NO_MORE_TICKETS,
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'code' => ResponseCode::GENERAL_EXCEPTION,
            'message' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
