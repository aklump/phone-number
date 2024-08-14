<?php

namespace AKlump\PhoneNumber;

class Violation {

  /**
   * @var string
   */
  private $message;

  public function __construct(string $message) {
    $this->message = $message;
  }

  public function getMessage(): string {
    return $this->message;
  }

}
