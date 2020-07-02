# php-mailer
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
