<?php

namespace Ouranoshong\Mailer;

interface TransportInterface
{
    public function send($from, $to, $message, $headers = null);
}
