# php-mailer
[![Latest Stable Version](https://poser.pugx.org/phpunit/phpunit/v)](//packagist.org/packages/phpunit/phpunit)
[![Total Downloads](https://poser.pugx.org/phpunit/phpunit/downloads)](//packagist.org/packages/phpunit/phpunit)
[![Latest Unstable Version](https://poser.pugx.org/phpunit/phpunit/v/unstable)](//packagist.org/packages/phpunit/phpunit)
[![License](https://poser.pugx.org/phpunit/phpunit/license)](//packagist.org/packages/phpunit/phpunit)
[![Build Status](https://travis-ci.org/ouranoshong/php-mailer.svg?branch=master)](https://travis-ci.org/ouranoshong/php-mailer)
[![Coverage Status](https://coveralls.io/repos/github/ouranoshong/php-mailer/badge.svg?branch=master)](https://coveralls.io/github/ouranoshong/php-mailer?branch=master)

send email with smtp (support http proxy)


### Install
```shell
composer require ouranoshong/php-mailer
```

### Usage
```php
<?php

$transport = new \Ouranoshong\Mailer\SMTPTransport(
    [
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'your username',
        'password' => 'your password',
        
        'httpProxy' => 'http://proxy.com:8080' //user http proxy
    ]
);

$mailer = new \Ouranoshong\Mailer\Mailer($transport);
$mailer->setFrom('from@example.com')
    ->setTo('to@example.com')
    ->setSubject('subject')
    ->setText('email from php mailer')
    ->send();

```
