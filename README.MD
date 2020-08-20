# PHP Client for DNSLINQ API

A simple PHP client to use the [DNSLINQ API](https://dnslinq.de/).

We demonstrate a few common tasks that arise when reselling domains. 

This sample client can be used as a first step in getting started with using the DNSLINQ API in your third party software.

This example therefore is by no means complete and should be seen as a quick introduction.

## Requirements

* PHP >= 7.1
* Composer 
* Guzzle HTTP Client

## Installation

In this example, we will use [Guzzle](https://github.com/guzzle/guzzle) as the HTTP client library implementation.

`api-php-client` uses [Composer](http://getcomposer.org).
The first step to use `api-php-client` is to download composer:

```bash
$ curl -s http://getcomposer.org/installer | php
``` 

## Documentation

The full API documentation is available on the [API website](https://api.dnslinq.de/api/docs).

You can also generate a swagger scheme there in order to import the API definitions into software like Postman or go as far as generating a client library for your target programming language.

## Getting started

### Initialise the client
You first need to initialise the client with your credentials, which is essentially a JWT client bearer token.

If you don't have any user information, let's take a look at [this page](https://dnslinq.de) to request a Reseller Account for you.

## Support

If you find a bug or want to submit an improvement, please do not hesitate to raise an issue on Github.

In case you need any help with implementing the API, please feel free to contact the DNSLINQ team.
