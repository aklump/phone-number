<?php

namespace AKlump\PhoneNumber;

class PhoneNumberViolations {

  const NO_COUNTRY_CODE = 1;

  const NO_COUNTRY_CODE_MESSAGE = 'The phone number is missing the country code.';

  const NO_AREA_CODE = 2;

  const NO_AREA_CODE_MESSAGE = 'The phone number is missing the %d-digit area code.';

  const TOO_SHORT = 3;

  const TOO_SHORT_MESSAGE = 'The phone number must be at least %d digit(s) long.';
}
