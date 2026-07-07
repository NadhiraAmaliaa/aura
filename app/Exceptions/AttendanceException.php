<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business-rule violation while recording attendance (check-in / check-out).
 *
 * Carries a user-safe Indonesian [getMessage] and the HTTP status the API
 * should respond with. The web controller surfaces the message via a flash
 * error; the API maps [status] to the JSON error response.
 */
class AttendanceException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 422)
    {
        parent::__construct($message);
    }

    /**
     * A rule was violated (e.g. past the check-in deadline, already on leave).
     */
    public static function unprocessable(string $message): self
    {
        return new self($message, 422);
    }

    /**
     * The action conflicts with existing state (e.g. already checked in today).
     */
    public static function conflict(string $message): self
    {
        return new self($message, 409);
    }
}
