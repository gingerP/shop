<?php

namespace errors;


class BaseError extends \Error
{
    private $errorMessage = '';
    protected $code;
    private $stack = '';

    public function __construct($message, $code) {
        $this->errorMessage = $message;
        if ($message instanceof Error) {
            $this->errorMessage = $message->getMessage();
        } else if ($message instanceof Exception) {
            $this->errorMessage = $message->getMessage();
            $this->stack = $message->getTraceAsString();
        }
        $this->code = $code;
    }

}