# Thin wrapper for Fixer.io

A thin wrapper for [Fixer.io](http://www.fixer.io), a service for foreign exchange rates and currency conversion. It provides a few methods to easily construct the url, makes the api call and gives back the response.

## Installation

- Add the package to your composer.json file and run `composer update`:
```json
{
    "require": {
        "fadion/fixerio": "dev-master"
    }
}
```

Laravel users can use the Facade for even easier access.

- Add `Fadion\Fixerio\ExchangeServiceProvider` to your `app/config/app.php` file, inside the `providers` array.
- Add a new alias: 'Exchange' => 'Fadion\Fixerio\Facades\Exchange' to your `app/config/app.php` file, inside the `aliases` array.

## Usage

Let's get the rates of EUR and GBP with USD as the base currency:

```php
use Fadion\Fixerio\Exchange;
use Fadion\Fixerio\Currency;

$exchange = new Exchange();
$exchange->base(Currency::USD);
$exchange->symbols(Currency::EUR, Currency::GBP);

$rates = $exchange->get();
```

By default, the base currency is `EUR`, so if that's your base, there's no need to set it. The symbols can be omitted too, as Fixer will return every supported currency.

A simplified example without the base and currency:

```php
$rates = (new Exchange())->get();
```

The `historical` option will return currency rates for every day since the date you've specified. The base currency and symbols can be omitted here to, but let's see a full example:

```php
$exchange = new Exchange();
$exchange->historical('2012-12-12');
$exchange->base(Currency::AUD);
$exchange->symbols(Currency::USD, Currency::EUR, Currency::GBP);

$rates = $exchange->get();
```

Finally, you may have noticed the use of the `Currency` class with currencies as constants. It's just a convenience to prevent errors from typos, but they're completely optional.

This:

```php
$exchange->base(Currency::AUD);
$exchange->symbols(Currency::USD, Currency::EUR, Currency::GBP);
```

is equivalent to:

```php
$exchange->base('AUD');
$exchange->symbols('USD', 'EUR', 'GBP');
```

Use whatever methods fills your needs.

## TODO

- Write unit tests.