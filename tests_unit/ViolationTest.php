<?php

namespace AKlump\PhoneNumber\Tests\Unit;

use AKlump\PhoneNumber\Violation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhoneNumber\Violation
 */
class ViolationTest extends TestCase {

  public function testGetMessage() {
    $message = 'foo bar baz';
    $violation = new Violation($message);
    $this->assertSame($message, $violation->getMessage());
  }
}
