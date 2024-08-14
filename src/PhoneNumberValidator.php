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

    $area_code_length = $this->model->areaCode()['length'] ?? 0;
    $country_code_length = $this->model->countryCode()['length'] ?? 0;
    $country_code_value = $this->model->countryCode()['value'] ?? 0;
    $min_chars = $this->getMinimumRequiredInputLength($data,
      $country_code_length,
      $country_code_value,
      $area_code_length,
      $data['format_has_area_code'],
      $data['format_has_country_code']
    );

    $this->checkLengthViolation($data['digits_only'], $min_chars, $violations);
    $this->checkAreaCodeViolation($data, $violations);
    $this->checkCountryCodeViolation($data, $country_code_length, $violations);

    return $violations;
  }

  private function getMinimumRequiredInputLength(array $data, int $country_code_length, $country_code_value, int $area_code_length, bool $format_has_area_code, bool $format_has_country_code): int {
    $countable = $data['format'];
    $countable = str_replace(['#CC#', '#c#'], '', $countable);
    $min_chars = substr_count($countable, '#');
    if ($format_has_area_code) {
      $min_chars += $area_code_length;
    }
    if ($format_has_country_code) {
      $has_default_country_code = isset($country_code_value);
      if (!$has_default_country_code || empty($data['parsed']['country_code'])) {
        $min_chars += $country_code_length;
      }
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
      $violations[PhoneNumberViolations::TOO_SHORT] = new Violation(sprintf(PhoneNumberViolations::TOO_SHORT_MESSAGE, $min_chars));
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
      $violations[PhoneNumberViolations::NO_AREA_CODE] = new Violation(sprintf(PhoneNumberViolations::NO_AREA_CODE_MESSAGE, $area_code_length));
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
      $violations[PhoneNumberViolations::NO_COUNTRY_CODE] = new Violation(sprintf(PhoneNumberViolations::NO_COUNTRY_CODE_MESSAGE, $country_code_length));
    }
  }

}
