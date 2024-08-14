<?php

namespace AKlump\PhoneNumber\Models;

/**
 * All methods must return an array with these possible keys
 * - length int The required length or violation.  THIS IS REQUIRED
 * - value int The required value  THIS IS OPTIONAL.  If provided it will be
 * assumed and constrained.
 */
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
