# Phone Number

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
    composer require aklump/phone-number:@dev
    ```

## Usage

```php
$default_area_code = 503;
$formatter = (new \AKlump\PhoneNumber\FormatUSPhoneNumber($default_area_code);

$formatted = $formatter('8881223');
// '(503) 888-1223' === $formatted

$formatted = $formatter('888-1223', \AKlump\PhoneNumber\PhoneFormats::SMS);
// '+15038881223' === $formatted

// Provide a custom default format.
$formatter = (new \AKlump\PhoneNumber\FormatUSPhoneNumber(503, '+#CC#.#c#.###.####');
$formatted = $formatter('888-1223');
// '+1.503.888.1223' === $formatted
```

## International Numbers

See [this project](https://github.com/dmamontov/phone-normalizer/tree/master)
