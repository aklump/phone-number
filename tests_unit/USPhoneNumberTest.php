<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber\Tests\Unit;

use AKlump\PhoneNumber\Models\USPhoneNumberModel;
use AKlump\PhoneNumber\PhoneNumberViolations;
use AKlump\PhoneNumber\USPhoneNumber;
use AKlump\PhoneNumber\PhoneFormats;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhoneNumber\USPhoneNumber
 * @uses   \AKlump\PhoneNumber\Models\USPhoneNumberModel
 * @uses   \AKlump\PhoneNumber\PhoneNumberValidator
 * @uses   \AKlump\PhoneNumber\Violation
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
    $phone = new USPhoneNumber();
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
    $phone = new USPhoneNumber();
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
      PhoneFormats::JSON,
    ];
    $tests[] = [
      '888.2746',
      '8882746',
      '###.####',
    ];
    $tests[] = [
      '+19715630360',
      '9715630360',
      PhoneFormats::SMS,
    ];

    $tests[] = [
      '+15032974755',
      '5032974755',
      PhoneFormats::SMS,
    ];


    $tests[] = [
      '+15032974755',
      '5032974755',
      PhoneFormats::SMS,
    ];

    $tests[] = [
      '+15032974755',
      '+15032974755',
      '+1#c########',
    ];
    $tests[] = [
      '(360) 888-2741',
      '360.888.2741',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2742',
      '3608882742',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2743',
      '13608882743',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2744',
      '+13608882744',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2745',
      '1.360.888.2745',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '(360) 888-2746',
      '360-888-2746',
      PhoneFormats::NANP,
    ];
    $tests[] = [
      '+1 360 888 2746',
      '360-888-2746',
      PhoneFormats::E164,
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
    $this->assertSame($expected, (new USPhoneNumber())->format($subject, $format));
  }

  public function testMissingAreaCodeThrowsInvalidArgumentException() {
    $this->expectException(InvalidArgumentException::class);
    (new USPhoneNumber())->format('888-1223', PhoneFormats::SMS);
  }

  public function dataFortestValidateFindsNoViolationsProvider(): array {
    $tests = [];
    $tests[] = [3605551212, '#CC#-#c#-###-####'];
    $tests[] = [5551212, '###.####'];
    $tests[] = [5551212, '####'];
    $tests[] = [3605551212, '#c#-###-####'];
    $tests[] = [3605551212, PhoneFormats::NANP];

    return $tests;
  }

  /**
   * @dataProvider dataFortestValidateFindsNoViolationsProvider
   */
  public function testValidateFindsNoViolations($number, string $format) {
    $phone = new USPhoneNumber();
    $violations = $phone->validate($number, $format);
    $this->assertEmpty($violations);
  }

  public function testValidateMissingCountryCodeRequiredDoesNotViolateBecauseOfDefaultValueUS() {
    $phone = new USPhoneNumber();
    $violations = $phone->validate(3605551212, PhoneFormats::E164);
    $this->assertCount(0, $violations);
  }

  public function testValidateMissingAreaCodeRequiredFindsTwoViolations() {
    $phone = new USPhoneNumber();
    $violations = $phone->validate(5551212, PhoneFormats::NANP);
    $this->assertCount(2, $violations);
    $this->assertMatchesRegularExpression('#3-digit#', $violations[PhoneNumberViolations::NO_AREA_CODE]->getMessage());
    $this->assertMatchesRegularExpression('#10 digit#', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testValidateMissingAreaCodeRequiredFindsTwoViolationsWithCorrectMessages() {
    $phone = new USPhoneNumber();
    $violations = $phone->validate(5551212, PhoneFormats::SMS);
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
    $phone = new USPhoneNumber();
    $violations = $phone->validate($number, $format);
    $this->assertGreaterThanOrEqual(1, count($violations));
    $this->assertMatchesRegularExpression('/' . $pattern . '/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testValidateResetsViolationsOnSubsequentCalls() {
    $phone = new USPhoneNumber(NULL, 360);
    $violations = $phone->validate('1212');
    $this->assertNotEmpty($violations);
    $violations = $phone->validate('5551212');
    $this->assertEmpty($violations);
  }

  public function testCorrectDigitRequirementBasedOnModel() {
    $violations = (new USPhoneNumber(PhoneFormats::NANP, NULL, new USPhoneNumberModel()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
    $violations = (new USPhoneNumber(PhoneFormats::SMS, NULL, new USPhoneNumberModel()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

  public function testCorrectDigitRequirementBasedOnModelWithoutDefaultCountryCode() {
    $violations = (new USPhoneNumber(PhoneFormats::NANP, NULL, new USPhoneNumberModelNoDefaultCountryCode()))->validate('5551212');
    $this->assertMatchesRegularExpression('/10 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
    $violations = (new USPhoneNumber(PhoneFormats::SMS, NULL, new USPhoneNumberModelNoDefaultCountryCode()))->validate('5551212');
    $this->assertMatchesRegularExpression('/11 digit/', $violations[PhoneNumberViolations::TOO_SHORT]->getMessage());
  }

}

class USPhoneNumberModelNoDefaultCountryCode extends USPhoneNumberModel {

  public function countryCode(): array {
    return ['length' => 1];
  }

}
