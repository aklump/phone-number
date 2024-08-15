<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber;

use AKlump\PhoneNumber\Models\PhoneNumberModelInterface;
use AKlump\PhoneNumber\Models\USPhoneNumberModel;
use InvalidArgumentException;

final class USPhoneNumberFormatter {

  private $areaCode;

  private $countryCode;

  private $phoneNumberFormat;

  private $phoneNumberModel;

  private $validator;

  /**
   * Constructor method for the class.
   *
   * @param string|NULL $format_template , e.g. '+#CC# (#c#) ###-####'
   * - '#CC#' = country_code
   * - '#c#' = area area_code
   * - '###' = local_exchange (to the left)
   * - '####' = subscriber_number (to the right)
   * @param int|null $default_area_code Optional.  Pass this only if you want
   * numbers missing area codes to be valid by inserted this value.
   * @param PhoneNumberModelInterface|null $phone_number_model The phone number model. Default: \AKlump\PhoneNumber\Models\USPhoneNumberModel.
   *
   * @return void
   */
  public function __construct(string $format_template = NULL, int $default_area_code = NULL, PhoneNumberModelInterface $phone_number_model = NULL) {
    $this->areaCode = $default_area_code;
    $this->phoneNumberFormat = $format_template ?? PhoneNumberFormats::NANP;
    $this->phoneNumberModel = $phone_number_model ?? new USPhoneNumberModel();
    $this->countryCode = $this->phoneNumberModel->countryCode()['value'] ?? NULL;
    $this->validator = new PhoneNumberValidator($this->phoneNumberModel);
  }

  /**
   * Validate if a number is valid for a format.
   *
   * @param string $number
   *
   * @return \AKlump\PhoneNumber\Violation[] An array of any constraint violations.  If this is empty the number is valid for the format.
   *
   */
  public function validate(string $number): array {
    $data = $this->prepareData($number);

    return $this->validator->validate($data);
  }

  public function isValid(string $number): bool {
    $violations = $this->validate($number);

    return empty($violations);
  }

  /**
   * Format a US phone number
   *
   * @param string $number
   *
   * @return string
   *
   * @throws \InvalidArgumentException When the number is invalid.
   *
   * @see \AKlump\PhoneNumber\PhoneNumberFormats
   * @see \AKlump\PhoneNumber\USPhoneNumberFormatter::validate()
   */
  public function format(string $number): string {
    $data = $this->prepareData($number);
    $violations = $this->validator->validate($data);
    if (isset($violations[PhoneNumberViolations::NO_AREA_CODE])) {
      throw new InvalidArgumentException("Invalid US phone number.");
    }

    $formatted = $this->serializeDataWithTokenReplacement($data);

    return preg_replace('/###/', $data['parsed']['local_exchange'] ?? '###', $formatted, 1);
  }

  private function prepareData(string $number): array {
    $data = [];
    $data['format'] = $this->phoneNumberFormat;
    $data['format_has_area_code'] = strpos($data['format'], '#c#') !== FALSE;
    $data['format_has_country_code'] = strpos($data['format'], '#CC#') !== FALSE;

    $digits_only = $this->stripNonNumericChars($number);
    $data['original'] = [
      'number' => $number,
      'digits_only' => $digits_only,
    ];
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

  public function serializeDataWithTokenReplacement(array $data): string {
    $formatted = $data['format'];
    $formatted = str_replace('#CC#', $data['parsed']['country_code'] ?? '#CC#', $formatted);
    $formatted = str_replace('#c#', $data['parsed']['area_code'] ?? '#c#', $formatted);
    $formatted = preg_replace('/####([^#]*$)/', ($data['parsed']['subscriber_number'] ?? '####') . '$1', $formatted, 1);

    return $formatted;
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
      $this->phoneNumberModel->subscriberNumber()['length'] ?? 0,
      $this->phoneNumberModel->localExchange()['length'] ?? 0,
      $this->phoneNumberModel->areaCode()['length'] ?? 0,
      $this->phoneNumberModel->countryCode()['length'] ?? 0,
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
