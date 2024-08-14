<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber;

use InvalidArgumentException;

class FormatUSPhoneNumber {

  private $areaCode;

  private $defaultFormat;

  public function __construct(int $default_area_code = NULL, string $default_format = NULL) {
    $this->areaCode = $default_area_code;
    $this->defaultFormat = $default_format ?? PhoneFormats::NANP;
  }

  /**
   * Format a US phone number
   *
   * @param string $number
   * @param string|NULL $format , e.g. '+#CC# (#c#) ###-####'
   * - '#CC#' = country_code
   * - '#c#' = area area_code
   * - '###' = local_exchange (to the left)
   * - '####' = subscriber_number (to the right)
   *
   * @return string
   *
   * @see \AKlump\PhoneNumber\PhoneFormats
   */
  public function __invoke(string $number, string $format = NULL) {
    $format = $format ?? $this->defaultFormat;
    $stripped = $this->stripNonNumericChars($number);
    $parsed = $this->parsePhoneNumber($stripped);
    $parsed['country_code'] = $parsed['country_code'] ?? 1;

    // Allow missing area codes, if the format doesn't call for it.
    $area_code_is_required = (bool) strpos($format, '#c#');
    if ($area_code_is_required && empty($parsed['area_code'])) {
      throw new InvalidArgumentException(sprintf('Missing 3-digit area code in %s', $stripped));
    }
    $parsed['area_code'] = $parsed['area_code'] ?? $this->areaCode;

    return $this->format($parsed, $format);
  }

  private function stripNonNumericChars(string $number): int {
    return (int) preg_replace('#\D#', '', $number);
  }

  private function parsePhoneNumber(int $number): array {
    $temp = $number;
    $chunk_sizes = [4, 3, 3, 1];
    $chunk_names = [
      'subscriber_number',
      'local_exchange',
      'area_code',
      'country_code',
    ];
    $result = array_fill_keys($chunk_names, NULL);
    while (strlen($temp) && $chunk_size = array_shift($chunk_sizes)) {
      $chunk_name = array_shift($chunk_names);
      $result[$chunk_name] = substr($temp, -1 * $chunk_size);
      $temp = substr($temp, 0, -1 * $chunk_size);
    }

    return $result;
  }

  private function format(array $parsed, string $format): string {
    $result = $format;
    $result = str_replace('#CC#', $parsed['country_code'] ?? '#CC#', $result);
    $result = str_replace('#c#', $parsed['area_code'] ?? '#c#', $result);
    $result = preg_replace('/####([^#]*$)/', ($parsed['subscriber_number'] ?? '####') . '$1', $result, 1);
    $result = preg_replace('/###/', $parsed['local_exchange'] ?? '###', $result, 1);

    return $result;
  }

}
