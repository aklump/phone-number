# Phone Number

A lean, non-dependency PHP library to work with phone numbers.

![aklump/phone-number](images/aklump_phone_number.jpg)

## Install with Composer

1. Because this is an unpublished package, you must define it's repository in
   your project's _composer.json_ file. Add the following to _composer.json_ in
   the `repositories` array:
   
    ```json
    {
     "type": "github",
     "url": "https://github.com/aklump/phone-number"
    }
    ```
1. Require this package:
   
    ```
    composer require aklump/phone-number:^0.0
    ```

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
