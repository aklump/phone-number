<?php
// SPDX-License-Identifier: BSD-3-Clause

namespace AKlump\PhoneNumber\Tests;

use AKlump\PhoneNumber\FormatUSPhoneNumber;
use AKlump\PhoneNumber\PhoneFormats;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhoneNumber\FormatUSPhoneNumber
 */
final class FormatUSPhoneNumberTest extends TestCase {

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
  public function testInvoke(string $expected, $subject, string $format = NULL) {
    $this->assertSame($expected, (new FormatUSPhoneNumber())($subject, $format));
  }

  public function testMissingAreaCodeThrowsInvalidArgumentException() {
    $this->expectException(InvalidArgumentException::class);
    (new FormatUSPhoneNumber())('888-1223', PhoneFormats::SMS);
  }

}
