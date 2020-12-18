# php-mailer
[![Latest Stable Version](https://poser.pugx.org/ouranoshong/php-mailer/v)](//packagist.org/packages/ouranoshong/php-mailer) [![Total Downloads](https://poser.pugx.org/ouranoshong/php-mailer/downloads)](//packagist.org/packages/ouranoshong/php-mailer) [![Latest Unstable Version](https://poser.pugx.org/ouranoshong/php-mailer/v/unstable)](//packagist.org/packages/ouranoshong/php-mailer) [![License](https://poser.pugx.org/ouranoshong/php-mailer/license)](//packagist.org/packages/ouranoshong/php-mailer)
[![Build Status](https://www.travis-ci.com/ouranoshong/php-mailer.svg?branch=master)](https://www.travis-ci.com/ouranoshong/php-mailer)
[![Coverage Status](https://coveralls.io/repos/github/ouranoshong/php-mailer/badge.svg?branch=master)](https://coveralls.io/github/ouranoshong/php-mailer?branch=master)

send email with smtp (support http proxy)


### Install
```shell
composer require ouranoshong/php-mailer
```

### Usage

#### Simple usage demo
```php
<?php

$transport = new \Ouranoshong\Mailer\SMTPTransport(
    [
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'your username',
        'password' => 'your password'
    ]
);

$mailer = new \Ouranoshong\Mailer\Mailer($transport);
$mailer->setFrom('from@example.com')
    ->setTo('to@example.com')
    ->setSubject('subject')
    ->setText('email from php mailer')
    ->send();

```

#### Http proxy usage demo
```php
$transport = new \Ouranoshong\Mailer\SMTPTransport(
    [
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'your username',
        'password' => 'your password',
        
        'httpProxy' => 'http://proxy.com:8080' //use http proxy
    ]
);

$mailer = new \Ouranoshong\Mailer\Mailer($transport);
$mailer->setFrom('from@example.com')
    ->setTo('to@example.com')
    ->setSubject('subject')
    ->setText('email from php mailer')
    ->send();
```
