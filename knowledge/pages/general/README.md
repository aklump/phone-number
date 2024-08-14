<!--
id: readme
tags: ''
-->

# Phone Number

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
```

## International Phone Numbers

See [this project](https://github.com/dmamontov/phone-normalizer/tree/master)
