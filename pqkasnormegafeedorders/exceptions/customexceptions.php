<?php

class ISOCountryNotFoundException extends Exception
{

    public function __construct($message, $code = 1, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {

        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class ISOStateNotFoundException extends Exception
{

    public function __construct($message, $code = 2, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {

        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class KasnorCustomerNotAllowedException extends Exception
{

    public function __construct($message, $code = 2, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {

        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class AddressNotValidException extends Exception
{

    public function __construct($message, $code = 2, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {

        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class NotStockException extends Exception
{

    public function __construct($message, $code = 2, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {

        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}