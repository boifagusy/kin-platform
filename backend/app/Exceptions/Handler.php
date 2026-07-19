<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function context(): array
    {
        return array_merge(parent::context(), [
            'user_id' => auth()->id(),
            'route' => request()->route()?->getName(),
        ]);
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    protected function convertExceptionToArray(Throwable $e): array
    {
        if (config('app.debug')) {
            return parent::convertExceptionToArray($e);
        }

        return [
            'message' => $this->isHttpException($e)
                ? $e->getMessage()
                : 'Server Error',
        ];
    }
}
