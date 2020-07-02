<?php

namespace Ouranoshong\Mailer;

final class Constant
{
    public const EOF = "\r\n";
    public const DEBUG_ON = 1;

    //'normal', 'urgent', 'non-urgent'
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_NON_URGENT = 'no-urgent';
}
