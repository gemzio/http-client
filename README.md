# Gemz Http-Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gemz/http-client.svg?style=flat-square)](https://packagist.org/packages/gemz/http-client)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/gemzio/http-client/run-tests?label=tests)](https://github.com/gemzio/http-client/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Quality Score](https://img.shields.io/scrutinizer/g/gemzio/http-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/gemzio/http-client)
[![Total Downloads](https://img.shields.io/packagist/dt/gemz/http-client.svg?style=flat-square)](https://packagist.org/packages/gemz/http-client)


Gemz Http Client is a simple Symfony Http-Client wrapper to provide an easy development experience for most use cases.

If you need more functionality, just use [Guzzle](https://github.com/guzzle/guzzle) or 
[Symfony](https://github.com/symfony/http-client) clients.

## Installation

You can install the package via composer:

```bash
composer require gemz/http-client
```

## Instantiation

```php
use Gemz\HttpCLient\Client;

// client
$client = Client::create();
$client = new Client();

// with configuration options
$config = Config::build()->baseUri('https://myapi.com');
$client = Client::create($config);

$response = $client->get('users');
echo $response->status();
```

## Basic Usage

You can configure the client with initial values. Configuration options are valid for all requests made with the 
client object unless you override the options.

```php
use Gemz\HttpCLient\Client;
use Gemz\HttpCLient\Config;

$config = Config::build()
    ->timeout(1.5)
    ->header('x-api-key', 'myapikey')
    ->authBasic('username', 'password')
    ->baseUri('https://myapi.com');
 
$client = Client::create($config);

$response = $client->get('users');

// you can override all values from config
$response = $client
    ->timeout(5.5)
    ->get('users');
```

> The only default option is content-type => application/json

## Request And Configuration Options

> Be aware that all requests are made `asynchronous`.

```php
// authentication
// password in auth basic is optional
$client->authBasic('<username', '<password');
$client->authBearer('<token');

// headers
$client->header('<key>', '<value');
$client->headers(['<key>' => 'value']);

// content type
$client->contentType('<contentType');

// useragent
$client->userAgent('<userAgent');

// query params
$client->queryParam('<key>', '<value');
$client->queryParams(['<key>' => 'value']);

// timeout
$client->timeout(<float>);

// max redirects - 0 means unlimited
$client->maxRedirects(<integer>);

// max request <-> response duration
$client->maxDuration(<float>);

// throw errors if response is different than 200 - 299 status code
$client->throwErrors();

// ignores the configuration settings
$client->ignoreConfig(); 

// body format
$client->asJson(); // default
$client->asFormParams();
$client->asMultipart();
$client->asPlain();

// payload - depends on body format
// multipart form data will automatically transformed in the correct format
$client->payload(['<key>' => '<value']);
$client->payload('my message');

// without verifing ssl
$client->withoutVerifying();

// methods
$client->get('users/1');
$client->post('users');
$client->put('users/1');
$client->patch('users/1');
$client->delete('users/1');

// Symfony client options
$client->option('<key', '<value');
```

## Response

```php
$response = $client->get('users/1');

// requests are fully asynchron unless you read the content, headers or status code of the response 

// content access
$response->asArray();
$response->asObject();
$response->asCollection(); // illuminate collection
$response->asString();
$response->body();

// status code
$response->status();

// true / false
$response->isOk();
$response->isSuccess();
$response->isRedirect();
$response->isClientError();
$response->isServerError();

// headers
$response->header('content-type');
$response->headers();
$response->contentType();
$response->isJson();

// request url
$response->requestUrl();

// execution time
$response->executionTime();

// custom data
// the data from request custom data 
$response->customData();

// response infos
// returns info coming from the transport layer, such as "response_headers",
// "redirect_count", "start_time", "redirect_url", etc.
$response->info();

// symmfony response object
$response->response();

```

## Asynchronous Requests

```php
$client = Client::create(Config::build()->baseUri('https://myapi.com'));

$responses = [];

for ($i = 0; $i < 100; $i++) {
    $responses[] = $client->get("users/{$i}");
}

foreach ($responses as $response) {
    // ...
    $content = $response->asArray();
    // ...
}

``` 

## Testing

``` bash
composer test
composer test-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email stefan@sriehl.com instead of using the issue tracker.

## Credits

- [Stefan Riehl](https://github.com/stefanriehl)

## Support us

Gemz.io is maintained by [Stefan Riehl](https://github.com/stefanriehl). You'll find all open source
projects on [Gemz.io github](https://github.com/gemzio).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
