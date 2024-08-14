<?php

namespace AKlump\PhoneNumber\Models;

interface PhoneNumberModelInterface {

  /**
   * @return array With the key "length".
   */
  public function subscriberNumber(): array;

  /**
   * @return array With the key "length".
   */
  public function localExchange(): array;

  /**
   * @return array With the key "length".
   */
  public function areaCode(): array;

  /**
   * @return array With the key "length" and option"value"
   */
  public function countryCode(): array;
}
