<?php

namespace AKlump\PhoneNumber\Models;

class USPhoneNumberModel implements PhoneNumberModelInterface {

  public function subscriberNumber(): array {
    return ['length' => 4];
  }

  public function localExchange(): array {
    return ['length' => 3];
  }

  public function areaCode(): array {
    return ['length' => 3];
  }

  public function countryCode(): array {
    return ['length' => 1, 'default' => 1];
  }
}
