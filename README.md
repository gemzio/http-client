# Gemz Http-Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gemzio/http-client.svg?style=flat-square)](https://packagist.org/packages/gemzio/http-client)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/gemzio/http-client/run-tests?label=tests)](https://github.com/gemzio/http-client/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Quality Score](https://img.shields.io/scrutinizer/g/gemzio/http-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/gemzio/http-client)
[![Total Downloads](https://img.shields.io/packagist/dt/gemzio/http-client.svg?style=flat-square)](https://packagist.org/packages/gemzio/http-client)


Gemz Http Client is a simple Symfony Http-Client wrapper to provide an easy development experience for most use cases.

## Installation

You can install the package via composer:

```bash
composer require gemz/http-client
```

## Usage

``` php
use Gemz\HttpClient\Client;

$config = Config::build()
    ->useProxy()
    ->verifySsl(false)
    ->maxRedirects()
    ->timeout()
    ->authBasic()
    ->authBearer()
    ->authDigest()
    ->header($key, $value)
    ->baseUri($uri);

$client = Client::create($config);

$client->config()->header(key, value);

$response = $client
    ->ignoreConfig()
    ->queryParam($key, $value)
    ->queryParams(array $params)
    ->payload()
    ->put($endpoint)
    ->get($endpoint)
    ->request($method, $endpoint);

$client = Client::
    ->withBaseUri($url)
    ->withBearerAuth($token)
    ->withBasicAuth($username, $password)
    ->withDigestAuth($username, $password)
    ->withHeader($key, $value)
    ->withHeaders([$key => $value])
    ->withQueryParam($key, $value)
    ->withQueryParams([$key => $value])
    ->timeout()
    ->maxRedirects(5)
    ->withCookies()
    ->withOption($key, $value)
    ->withOptions(['proxy' => $proxy])
    ->get($endpoint)
    ->put($endpoint)
    ->post($endpoint)
    ->delete($endpoint)
    ->patch($endpoint)


```

### Testing

``` bash
composer test
composer test-coverage
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email stefan@sriehl.com instead of using the issue tracker.

## Credits

- [Stefan Riehl](https://github.com/stefanriehl)

## Support us

Gemz.io is maintained by [Stefan Riehl](https://github.com/stefanriehl). You'll find all open source
projects on [Gemz.io github](https://github.com/gemzio).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
