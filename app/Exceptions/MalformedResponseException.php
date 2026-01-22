<?php

namespace App\Exceptions;

use Exception;

class MalformedResponseException extends Exception
{
    public function __construct(
        string $message = 'Malformed AI response',
        public ?string $rawResponse = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get a summary of the raw response for logging
     */
    public function getRawResponseSummary(int $maxLength = 500): ?string
    {
        if ($this->rawResponse === null) {
            return null;
        }

        if (strlen($this->rawResponse) <= $maxLength) {
            return $this->rawResponse;
        }

        return substr($this->rawResponse, 0, $maxLength).'...';
    }
}
