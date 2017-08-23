# PSR-15 Middleware

[![Build Status](https://travis-ci.org/Grafikart/PSR15-CsrfMiddleware.svg?branch=master)](https://travis-ci.org/Grafikart/PSR15-CsrfMiddleware) [![Coverage Status](https://coveralls.io/repos/github/Grafikart/PSR15-CsrfMiddleware/badge.svg?branch=master)](https://coveralls.io/github/Grafikart/PSR15-CsrfMiddleware?branch=master)

This middleware check every POST, PUT or DELETE request for a CSRF token.
Token are persisted using an ArrayAccess compatible Session and are generated on demand.

```php
$middleware = new CsrfMiddleware($_SESSION, 200);
$app->pipe($middleware);

// Generate input
$input = "<input type=\"hidden\" name=\"{$middleware->getFormKey()}\" value=\"{$middleware->generateToken()}\"/>
```

Middleware are constructed with this parameters

- $session, **ArrayAccess|array**, used to store token
- $limit, **int**, limit the number of token to persist
- $sessionKey, **string**
- $formKey, **string**

