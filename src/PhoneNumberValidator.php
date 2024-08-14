<?php

namespace AKlump\PhoneNumber;

use AKlump\PhoneNumber\Models\PhoneNumberModelInterface;

class PhoneNumberValidator {

  /** @var \AKlump\PhoneNumber\Models\PhoneNumberModelInterface */
  protected $model;

  public function __construct(PhoneNumberModelInterface $model) {
    $this->model = $model;
  }

  /**
   * @param array $data
   *
   * @return array An array of any violations.
   */
  public function validate(array $data): array {
    $violations = [];

    $country_code_length = $this->model->countryCode()['length'] ?? 0;
    $min_chars = $this->getMinimumDigits($data['format'],
      $country_code_length,
      $data['format_has_area_code'],
      $data['format_has_country_code']
    );

    $this->checkLengthViolation($data['digits_only'], $min_chars, $violations);
    $this->checkAreaCodeViolation($data, $violations);
    $this->checkCountryCodeViolation($data, $country_code_length, $violations);

    return $violations;
  }

  private function getMinimumDigits(string $format, int $country_code_length, bool $format_has_area_code, bool $format_has_country_code): int {
    $countable = $format;
    $countable = str_replace(['#CC#', '#c#'], '', $countable);
    $min_chars = substr_count($countable, '#');
    if ($format_has_area_code) {
      $min_chars += 3;
    }
    if ($format_has_country_code) {
      $min_chars += $country_code_length;
    }

    return $min_chars;
  }

  /**
   * @param $digits_only
   * @param int $min_chars
   * @param array $violations
   *
   * @return array
   */
  public function checkLengthViolation($digits_only, int $min_chars, array &$violations): void {
    if (strlen($digits_only) < $min_chars) {
      $violations[PhoneNumberViolations::TOO_SHORT] = sprintf(PhoneNumberViolations::TOO_SHORT_MESSAGE, $min_chars);
    }
  }

  /**
   * @param array $data
   * @param array $violations
   *
   * @return void
   */
  public function checkAreaCodeViolation(array $data, array &$violations): void {
    if ($data['format_has_area_code']
      && ($area_code_length = $this->model->areaCode()['length'] ?? 0)
      && empty($data['parsed']['area_code'])) {
      $violations[PhoneNumberViolations::NO_AREA_CODE] = sprintf(PhoneNumberViolations::NO_AREA_CODE_MESSAGE, $area_code_length);
    }
  }

  /**
   * @param array $data
   * @param $country_code_length
   * @param array $violations
   *
   * @return array
   */
  public function checkCountryCodeViolation(array $data, $country_code_length, array &$violations): void {
    if ($data['format_has_country_code']
      && empty($data['parsed']['country_code'])) {
      $violations[PhoneNumberViolations::NO_COUNTRY_CODE] = sprintf(PhoneNumberViolations::NO_COUNTRY_CODE_MESSAGE, $country_code_length);
    }
  }

}
