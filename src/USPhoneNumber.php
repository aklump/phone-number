<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber;

use AKlump\PhoneNumber\Models\USPhoneNumberModel;
use InvalidArgumentException;

class USPhoneNumber {

  private $areaCode;

  private $countryCode;

  private $format;

  private $model;

  public function __construct(int $default_area_code = NULL, string $default_format = NULL) {
    $this->areaCode = $default_area_code;
    $this->format = $default_format ?? PhoneFormats::NANP;
    $this->model = new USPhoneNumberModel();
    $this->countryCode = $this->model->countryCode()['default'] ?? NULL;
  }

  /**
   * Validate if a number is valid for a format.
   *
   * @param string $number
   * @param string|NULL $format
   *
   * @return array An array of any constraint violations.  If this is empty the number is valid for the format.
   *
   */
  public function validate(string $number, string $format = NULL): array {
    $data = $this->prepareData($number, $format);

    return $this->getViolations($data);
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
   * @throws \InvalidArgumentException When the number is invalid.
   *
   * @see \AKlump\PhoneNumber\PhoneFormats
   * @see \AKlump\PhoneNumber\USPhoneNumber::validate()
   */
  public function format(string $number, string $format = NULL): string {
    $data = $this->prepareData($number, $format);
    $violations = $this->getViolations($data);
    if (isset($violations[PhoneNumberViolations::NO_AREA_CODE])) {
      throw new InvalidArgumentException("Invalid US phone number.");
    }

    $formatted = $data['format'];
    $formatted = str_replace('#CC#', $data['parsed']['country_code'] ?? '#CC#', $formatted);
    $formatted = str_replace('#c#', $data['parsed']['area_code'] ?? '#c#', $formatted);
    $formatted = preg_replace('/####([^#]*$)/', ($data['parsed']['subscriber_number'] ?? '####') . '$1', $formatted, 1);
    $formatted = preg_replace('/###/', $data['parsed']['local_exchange'] ?? '###', $formatted, 1);

    return $formatted;
  }

  private function getViolations(array $data): array {
    $violations = [];
    if ($data['format_has_area_code'] && empty($data['parsed']['area_code'])) {
      $violations[PhoneNumberViolations::NO_AREA_CODE] = sprintf(PhoneNumberViolations::NO_AREA_CODE_MESSAGE, 3);
    }
    if ($data['format_has_country_code'] && empty($data['parsed']['country_code'])) {
      $violations[PhoneNumberViolations::NO_COUNTRY_CODE] = PhoneNumberViolations::NO_COUNTRY_CODE_MESSAGE;
    }

    $min_chars = $this->getMinimumDigits($data['format'], $data['format_has_area_code'], $data['format_has_country_code']);
    if (strlen($data['digits_only']) < $min_chars) {
      $violations[PhoneNumberViolations::TOO_SHORT] = sprintf(PhoneNumberViolations::TOO_SHORT_MESSAGE, $min_chars);
    }

    return $violations;
  }

  private function getMinimumDigits(string $format, bool $format_has_area_code, bool $format_has_country_code): int {
    $foo = $format;
    $foo = str_replace(['#CC#', '#c#'], '', $foo);
    $min_chars = substr_count($foo, '#');
    if ($format_has_area_code) {
      $min_chars += 3;
    }
    if ($format_has_country_code) {
      $min_chars += strlen($this->countryCode);
    }

    return $min_chars;
  }

  private function prepareData(string $number, ?string $format): array {
    $data['format'] = $format ?? $this->format;
    $data['format_has_area_code'] = strpos($data['format'], '#c#') !== FALSE;
    $data['format_has_country_code'] = strpos($data['format'], '#CC#') !== FALSE;

    $digits_only = $this->stripNonNumericChars($number);
    $data['parsed'] = $this->parsePhoneNumber($digits_only);
    $this->fillInWithDefaults($data['parsed'], $data['format_has_area_code'], $data['format_has_country_code']);

    $data['digits_only'] = (int) implode([
      $data['parsed']['country_code'],
      $data['parsed']['area_code'],
      $data['parsed']['local_exchange'],
      $data['parsed']['subscriber_number'],
    ]);

    return $data;
  }


  private function fillInWithDefaults(array &$parsed, bool $format_has_area_code, bool $format_has_country_code) {
    if ($format_has_area_code && empty($parsed['area_code'])) {
      $parsed['area_code'] = $parsed['area_code'] ?? $this->areaCode;
    }
    if ($format_has_country_code && empty($parsed['country_code'])) {
      $parsed['country_code'] = $parsed['country_code'] ?? $this->countryCode;
    }
  }

  private function stripNonNumericChars(string $number): int {
    return (int) preg_replace('#\D#', '', $number);
  }

  private function parsePhoneNumber(int $number): array {
    $temp = $number;
    $chunk_names = [
      'subscriber_number',
      'local_exchange',
      'area_code',
      'country_code',
    ];
    $chunk_sizes = [
      $this->model->subscriberNumber()['length'] ?? 0,
      $this->model->localExchange()['length'] ?? 0,
      $this->model->areaCode()['length'] ?? 0,
      $this->model->countryCode()['length'] ?? 0,
    ];
    $result = array_fill_keys($chunk_names, NULL);
    while (strlen($temp) && $chunk_size = array_shift($chunk_sizes)) {
      $chunk_name = array_shift($chunk_names);
      $result[$chunk_name] = substr($temp, -1 * $chunk_size);
      $temp = substr($temp, 0, -1 * $chunk_size);
    }

    return $result;
  }

}
