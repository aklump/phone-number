<!--
id: readme
tags: ''
-->

# Phone Number

A lean, non-dependency PHP library to work with phone numbers. The focus of the library is on U.S. phone numbers only at this time. To work with international phone numbers you might try [Phone Normalizer](Phone Normalizer), from which we've taken the same formatting strategy. (Thank you, [dmamontov](https://github.com/dmamontov) and [1on](https://github.com/1on).)

![aklump/phone-number](../../images/aklump_phone_number.jpg)

{{ composer.install|raw }}

## Usage

```php
$default_area_code = 360;
$format = (new \AKlump\PhoneNumber\FormatUSPhoneNumber($default_area_code);

$number = $format('8881223');
// '(360) 888-1223' === $formatted

$number = $format('888-1223', \AKlump\PhoneNumber\PhoneFormats::SMS);
// '+13608881223' === $number

// Provide a custom default format.
$format = (new \AKlump\PhoneNumber\FormatUSPhoneNumber(360, '+#CC#.#c#.###.####');
$number = $format('888-1223');
// '+1.360.888.1223' === $number

// Convert to a JSON string.
$format = (new \AKlump\PhoneNumber\FormatUSPhoneNumber(360, \AKlump\PhoneNumber\PhoneFormats::JSON);
$number = $format('888-1223');
// '{"country":"+1","areaCode":206,"localExchange":555,"subscriberNumber":1212}' === $number
```
