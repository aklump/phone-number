# Phone Number

A lean, non-dependency PHP library to work with phone numbers. The focus of the library is on U.S. phone numbers only at this time. To work with international phone numbers you might try [Phone Normalizer](https://github.com/aklump/phone-number), from which we've taken the same formatting strategy. (Thank you, [dmamontov](https://github.com/dmamontov) and [1on](https://github.com/1on).)

![aklump/phone-number](images/aklump_phone_number.jpg)

## Install with Composer

1. Require this package:
   
    ```
    composer require aklump/phone-number:^0.0
    ```

## Usage

### Formatting Numbers

* Tokens are:
    * `#CC#` for the country code.
    * `#c#` for the area code
    * `###` (leftmost three) for the local exchange.
    * `####` (rightmost four) for subscriber number.
* Pre-defined formats provided by `\AKlump\PhoneNumber\PhoneFormats`  
* Invalid phone numbers will not format, but throw an exception.
* To obtain a list of violations for an invalid phone number use the `::validate` method.

```php
$phone = (new \AKlump\PhoneNumber\USPhoneNumber();
$number = $phone->format('3608881223');
// '(360) 888-1223' === $formatted
```

If the context of your app is regional, you maybe want to assume a default area code.

```php
$default_area_code = 360;
$phone = (new \AKlump\PhoneNumber\USPhoneNumber($default_area_code);

$number = $phone->format('8881223');
// '(360) 888-1223' === $formatted
```

#### Formatted for SMS

```php
$number = $phone->format('888-1223', \AKlump\PhoneNumber\PhoneFormats::SMS);
// '+13608881223' === $number
```

#### Using Custom Formats

```php
// Provide a custom default format.
$phone = (new \AKlump\PhoneNumber\USPhoneNumber(360, '+#CC#.#c#.###.####');
$number = $phone->format('888-1223');
// '+1.360.888.1223' === $number
```

#### Outside the Box Thinking

```php
// Convert to a JSON string.
$phone = (new \AKlump\PhoneNumber\USPhoneNumber(360, \AKlump\PhoneNumber\PhoneFormats::JSON);
$number = $phone->format('888-1223');
// '{"country":"+1","areaCode":206,"localExchange":555,"subscriberNumber":1212}' === $number
```

### Validating Numbers

```php
$phone = (new \AKlump\PhoneNumber\USPhoneNumber();
$violations = $phone->validate('3608881223');
foreach($violations as $violation) {
  echo $violation;
}
$is_valid = empty($violations);
```

* See also `\AKlump\PhoneNumber\PhoneNumberViolations`
