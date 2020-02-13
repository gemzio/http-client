# Gemz Http-Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gemz/http-client.svg?style=flat-square)](https://packagist.org/packages/gemz/http-client)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/gemzio/http-client/run-tests?label=tests)](https://github.com/gemzio/http-client/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Quality Score](https://img.shields.io/scrutinizer/g/gemzio/http-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/gemzio/http-client)
[![Total Downloads](https://img.shields.io/packagist/dt/gemz/http-client.svg?style=flat-square)](https://packagist.org/packages/gemz/http-client)


Gemz Http Client is a thin Symfony Http-Client wrapper to provide an easy development experience for most use cases.
Comes with easy to use multiplexing and concurrent requests.

If you need more functionality, just use [Guzzle](https://github.com/guzzle/guzzle) or 
[Symfony](https://github.com/symfony/http-client) clients.

## Installation

You can install the package via composer:

```bash
composer require gemz/http-client
```

## Basic Usage

````php
use Gemz\HttpCLient\Client;

$client = Client::create();

// get request
$response = $client->get('https://myapi.com/users');

// json post request
$response = $client
    ->payload(['name' => 'John'])
    ->post('https://myapi.com/users');

// query params
$response = $client
    ->queryParam('page', 23)
    ->get('https://myapi.com/users');
// calls https://myapi.com/users?page=20
````

## Client Initialization

You can configure the client with initial values. Configuration options are valid for all requests made with the 
client object unless you override the options in the request itself.

```php
use Gemz\HttpCLient\Client;

// Basic client with no config options
$client = Client::create();
$client = new Client();

// Client with config options
$config = Config::build()
    ->header('X-API-KEY', 'yourkey')
    ->baseUri('https://myapi.com');
    
$client = Client::create($config);
$resopnse = $client->get('users');
```

> All options are equal on client initialization and request itself. If you use the same option in config and the 
request, the request option will override the config option.  

That is great when you have to use different options in some requests other than in the config.

````php
$client = Client::create(Config::make()->header('X-KEY', 'yourkey'));

// use a different header
$response = $client->header('X-KEY', 'otherkey')->get('users');
````

## Default Settings

There are some default settings:

- Content-Type : 'application/json'
- All requests are made `asynchronous`
- Max Redirects 20
- Timeout defaults to ini_get('default_socket_timeout')
- Max Duration 0 means unlimited

## Configuration And Request Options

> Be aware that all requests are made `asynchronous`.

```php
// Authentication
$client->authBasic('<username', '<password');
$client->authBearer('<token');

// Headers
$client->header('<key>', '<value');
$client->headers(['<key>' => 'value']);

// Content-Type
$client->contentType('<contentType');

// User-Agent
$client->userAgent('<userAgent');

// Query Parameters
// Will result in ?key=value&key1=value1 ...
$client->queryParam('<key>', '<value');
$client->queryParams(['<key>' => 'value']);

// Timeout
$client->timeout(<float>);

// Max Redirects
// 0 means unlimited
$client->allowRedirects(<integer>);
$client->doNotAllowRedirects();

// Max Request <-> Response Duration
$client->maxDuration(<float>);

// Throw errors if response status code is >= 400
$client->throwErrors();

// Body Formats
// Default json
$client->asJson();
$client->asFormParams();
$client->asMultipart();
$client->asPlain();

// Payload - Depends On Body Format
// Multipart form data will automatically transformed in the correct format
// throws an exception if content-type and payload does not match
$client->payload(['<key>' => '<value']);
$client->payload('my message');

// without verifing ssl
// Default is verifing
$client->doNotVerifySsl();
$client->verifySsl();

// Proxy
$client->useProxy('tcp://...');

// Methods
// Endpoint can also be an URI
$client->get('users/1');
$client->get('https://myapi.com/users/1');
$client->post('users');
$client->put('users/1');
$client->patch('users/1');
$client->head('users/1');
$client->delete('users/1');

// Setting Symfony Specific Client Options
$client->option('<key', '<value');
```

You can also pass custom data to the request that will be available on the response. This is specially useful in 
multiple parallel requests to identify the origin request in the response.

```php
// can be string, array, object ....
$client->customData(['id' => 182736283]);

// in response
$response->customData(); // outputs ['id' => 182736283] 
```

## Response

> Requests are fully asynchronous unless you read the content, headers or status code of the response 

```php
$response = $client->get('users/1');

// Content Access
$response->asArray(); // if content is json
$response->asObject(); // if content is json
$response->asString();
$response->asStream(); // php resource
$response->body(); // same as asString()
$response->asCollection(); // // if content is json - illuminate collection

// Status Code
$response->status(); // integer
$response->isOk(); // true / false 200 - 299
$response->isSuccess(); // true / false 200 - 299
$response->isRedirect(); // true / false 300 - 399
$response->isClientError(); // true / false 400 - 499
$response->isServerError(); // true / false 500 - 599

// Headers
$response->header('content-type');
$response->headers();
$response->contentType();
$response->isJson(); // true / false

// Request Url
$response->requestUrl();

// Execution Time request <-> response
$response->executionTime();

// Custom Data
// Data from request custom data 
$response->customData();

// response infos
// returns info coming from the transport layer, such as "response_headers",
// "redirect_count", "start_time", "redirect_url", etc.
$response->info();

// symmfony response object
$response->response();

```

## Asynchronous Requests

Since all requests are asynchronous, you can put requests in an array and get the response later.
In this case the foreach loop get responses in order of the requests.

```php
$client = Client::create(
    Config::build()->baseUri('https://myapi.com')
);

$responses = [];

$responses[] = $client->get(sprintf($this->imgUrl, 2));
$responses[] = $client->get(sprintf($this->imgUrl, 3));
$responses[] = $client->get(sprintf($this->imgUrl, 4));

// responses are in order of the requests
foreach ($responses as $response) {
    echo $response->status());
}
```

If you do not know the ~ execution time of the request and have a lot of requests, this approach is much better and 
faster. The order of the responses is then independent of the request order. `This is fully asynchronous`.  

```php
$client = Client::create(
    Config::build()->baseUri('https://myapi.com')
);

$responses = [];

for ($i = 1; $i < 300; $i++) {
    $responses[] = $client
        ->customData('id:' . $i)
        ->get("users/{$i}");
}

// Using the Stream Object to access the responses

foreach ($client->stream($responses) as $response => $chunk) {
    Stream::from($response, $chunk)
        ->then(function (Response $response) {
            // success - do something with the response
        })
        ->timeout(function (Response $response) {
            // timeout - do something with the response        
        })
        ->catch(function ($exception, Response $response) {
            // exception was thrown
        });
}
``` 

### Stream Response And Chunk

Using the stream object is the fastest way to receive responses. You can listen to these events:

```php
use Gemz\HttpClient\Stream;

foreach ($client->stream($response) as $response => $chunk) {
    $stream = Stream::for($response, $chunk);
    
    // fulfilled
    // callable must use response
    $stream->then(/* callable */);

    // timeout
    // callable must use response
    $stream->timeout(/* callable */);

    // rejected
    // callable must use exception, response
    $stream->catch(/* callable */);
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
