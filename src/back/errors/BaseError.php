<?php

class BaseError extends Exception
{
    public $message = '';
    public $status;
    public $stack = '';

    public function __construct($message, $httpStatus) {
        $this->message = $message;
        if ($message instanceof Error) {
            $this->message = $message->getMessage();
        } else if ($message instanceof Exception) {
            $this->message = $message->getMessage();
            $this->stack = $message->getTraceAsString();
        }
        $this->status = $httpStatus;
    }

    public function toJson() {
        return [
            'message' => $this->message,
            'code' => $this->status
        ];
    }

}