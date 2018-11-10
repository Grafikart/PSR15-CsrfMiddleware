# PSR-15 Middleware

[![Build Status](https://travis-ci.org/Grafikart/PSR15-CsrfMiddleware.svg?branch=master)](https://travis-ci.org/Grafikart/PSR15-CsrfMiddleware) [![Coverage Status](https://coveralls.io/repos/github/Grafikart/PSR15-CsrfMiddleware/badge.svg?branch=master)](https://coveralls.io/github/Grafikart/PSR15-CsrfMiddleware?branch=master)

This middleware checks every POST, PUT and DELETE requests for a CSRF token.
Tokens are persisted using an ArrayAccess compatible Session and are generated on demand.

## Installation

```bash
composer require grafikart/psr15-csrf-middleware
```

## How to use it

```php
$middleware = new CsrfMiddleware($_SESSION, 200);
$app->pipe($middleware);

// Generate input
$input = "<input type=\"hidden\" name=\"{$middleware->getFormKey()}\" value=\"{$middleware->generateToken()}\"/>
```

Middleware is constructed with these parameters:

- session, **ArrayAccess|array**, used to store tokens
- limit, **int**, limits the amount of tokens the session is allowed to persist
- sessionKey, **string**
- formKey, **string**

