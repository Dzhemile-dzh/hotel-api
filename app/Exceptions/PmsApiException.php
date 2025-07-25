<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PmsApiException extends Exception
{
    protected $context;

    public function __construct(string $message, int $code = 0, array $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    /**
     * Get the exception's context information.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'PMS API Error',
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ], 500);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error('PMS API Exception', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ]);
    }
} 