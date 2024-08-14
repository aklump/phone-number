<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber\Tests\Unit;

use AKlump\PhoneNumber\Models\USPhoneNumberModel;
use AKlump\PhoneNumber\PhoneNumberViolations;
use AKlump\PhoneNumber\USPhoneNumberFormatter;
use AKlump\PhoneNumber\PhoneNumberFormats;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \AKlump\PhoneNumber\USPhoneNumberFormatter
 * @covers   \AKlump\PhoneNumber\PhoneNumberValidator
 * @covers   \AKlump\PhoneNumber\Models\USPhoneNumberModel
 * @uses     \AKlump\PhoneNumber\Violation
 */
final class USPhoneNumberTest extends TestCase {

  public function dataFortestIsValidReturnsTrueProvider() {
    $tests = [];
    $tests[] = [3605551212];
    $tests[] = [5551212, '###.####'];
    $tests[] = [1212, '####'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestIsValidReturnsTrueProvider
   */
  public function testIsValidReturnsTrue() {
    $phone = new USPhoneNumberFormatter();
    $this->assertTrue(call_user_func_array([
      $phone,
      'isValid',
    ], func_get_args()));
  }

  public function dataFortestIsValidReturnsFalseProvider() {
    $tests = [];
    $tests[] = [605551212];
    $tests[] = [551212, '###.####'];
    $tests[] = [212, '####'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestIsValidReturnsFalseProvider
   */
  public function testIsValidReturnsFalse() {
    $phone = new USPhoneNumberFormatter();
    $this->assertFalse(call_user_func_array([
      $phone,
      'isValid',
    ], func_get_args()));
  }

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      '{"country":"+1","areaCode":206,"localExchange":555,"subscriberNumber":1212}',
      '+1.206.555.1212',
      PhoneNumberFormats::JSON,
    ];
    $tests[] = [
      '888.2746',
      '8882746',
      '###.####',
    ];
    $tests[] = [
      '+19715630360',
      '9715630360',
      PhoneNumberFormats::SMS,
    ];

    $tests[] = [
      '+15032974755',
      '5032974755',
      PhoneNumberFormats::SMS,
    ];


    $tests[] = [
      '+15032974755',
      '5032974755',
      PhoneNumberFormats::SMS,
    ];

    $tests[] = [
      '+15032974755',
      '+15032974755',
      '+1#c########',
    ];
    $tests[] = [
      '(360) 888-2741',
      '360.888.2741',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2742',
      '3608882742',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2743',
      '13608882743',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2744',
      '+13608882744',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2745',
      '1.360.888.2745',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2746',
      '360-888-2746',
      PhoneNumberFormats::NANP,
    ];
    $tests[] = [
      '+1 360 888 2746',
      '360-888-2746',
      PhoneNumberFormats::E164,
    ];
    $tests[] = [
      '(360) 888-2746',
      '360-888-2746',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testFormat(string $expected, $subject, string $format = NULL) {
    $this->assertSame($expected, (new USPhoneNumberFormatter())->format($subject, $format));
  }

  public function testMissingAreaCodeThrowsInvalidArgumentException() {
    $this->expectException(InvalidArgumentException::class);
    (new USPhoneNumberFormatter())->format('888-1223', PhoneNumberFormats::SMS);
  }

  public function dataFortestValidateFindsNoViolationsProvider(): array {
    $tests = [];
    $tests[] = [3605551212, '#CC#-#c#-###-####'];
    $tests[] = [5551212, '###.####'];
    $tests[] = [5551212, '####'];
    $tests[] = [3605551212, '#c#-###-####'];
    $tests[] = [3605551212, PhoneNumberFormats::NANP];

    return $tests;
  }

  /**
   * @dataProvider dataFortestValidateFindsNoViolationsProvider
   */
  public function testValidateFindsNoViolations($number, string $format) {
    $phone = new USPhoneNumberFormatter();
    $violations = $phone->validate($number, $format);
    $this->assertEmpty($violations);
  }

  public function testValidateMissingCountryCodeRequiredDoesNotViolateBecauseOfDefaultValueUS() {
    $phone = new USPhoneNumberFormatter();
    $violations = $phone->validate(3605551212, PhoneNumberFormats::E164);
    $this->assertCount(0, $violations);
  }

  public function testValidateMissingAreaCodeRequiredFindsTwoViolations() {
    $phone = new USPhoneNumberFormatter();
    $violations = $phone->validate(5551212, PhoneNumberFormats::NANP);
    $this->assertCount(2, $violations);
    $this->assertMatchesRegularExpression('#3-digit#', $violations[PhoneNumberViolations::NO_AREA_CODE]->getMessage());
    $this->assertMatchesRegularExpression('#10 digit#', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testValidateMissingAreaCodeRequiredFindsTwoViolationsWithCorrectMessages() {
    $phone = new USPhoneNumberFormatter();
    $violations = $phone->validate(5551212, PhoneNumberFormats::SMS);
    $this->assertCount(2, $violations);
    $this->assertMatchesRegularExpression('#3-digit#', $violations[PhoneNumberViolations::NO_AREA_CODE]->getMessage());
    $this->assertMatchesRegularExpression('#10 digit#', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function dataFortestValidateTooShortReturnsExpectedViolationsProvider() {
    $tests = [];
    $tests[] = [551212, '###.####', '7 digit'];
    $tests[] = [551212, '#c#.###.####', '10 digit'];

    // Why is this also 10 digit; it's because our module has a default value
    // for the country code, so that digit is not required.
    $tests[] = [551212, '#CC#.#c#.###.####', '10 digit'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestValidateTooShortReturnsExpectedViolationsProvider
   */
  public function testValidateTooShortReturnsExpectedViolationMessage($number, string $format, string $pattern) {
    $phone = new USPhoneNumberFormatter();
    $violations = $phone->validate($number, $format);
    $this->assertGreaterThanOrEqual(1, count($violations));
    $this->assertMatchesRegularExpression('/' . $pattern . '/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testValidateResetsViolationsOnSubsequentCalls() {
    $phone = new USPhoneNumberFormatter(NULL, 360);
    $violations = $phone->validate('1212');
    $this->assertNotEmpty($violations);
    $violations = $phone->validate('5551212');
    $this->assertEmpty($violations);
  }

  public function testCorrectDigitRequirementBasedOnModel() {
    $violations = (new USPhoneNumberFormatter(PhoneNumberFormats::NANP, NULL, new USPhoneNumberModel()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
    $violations = (new USPhoneNumberFormatter(PhoneNumberFormats::SMS, NULL, new USPhoneNumberModel()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testCorrectDigitRequirementBasedOnModelWithoutDefaultCountryCode() {
    $violations = (new USPhoneNumberFormatter(PhoneNumberFormats::NANP, NULL, new USPhoneNumberModelNoDefaultCountryCode()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
    $violations = (new USPhoneNumberFormatter(PhoneNumberFormats::SMS, NULL, new USPhoneNumberModelNoDefaultCountryCode()))->validate('5551212');
    $this->assertMatchesRegularExpression('/11 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

}

class USPhoneNumberModelNoDefaultCountryCode extends USPhoneNumberModel {

  public function countryCode(): array {
    return ['length' => 1];
  }

}
