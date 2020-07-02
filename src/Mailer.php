<?php

namespace Ouranoshong\Mailer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Class Mailer
 * @package Ouranoshong\Mailer
 */
class Mailer
{
    /**
     * @var string
     */
    protected $toName;

    /**
     * @var string
     */
    protected $toEmail;

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var string
     */
    protected $replyName;

    /**
     * @var string
     */
    protected $replyEmail;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var array
     */
    protected $attachments;

    /**
     * @var string
     */
    protected $priority;

    /**
     * @var array
     */
    protected $customHeaders = [];

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $xMailer;

    /**
     * @var DateTimeImmutable
     */
    protected $date;

    private $contentIds = [];

    public function __construct(TransportInterface $transport)
    {
        $this->toName = '';
        $this->toEmail = '';
        $this->fromName = '';
        $this->fromEmail = '';
        $this->replyName = '';
        $this->replyEmail = '';
        $this->subject = '';
        $this->text = '';
        $this->html = '';
        $this->attachments = [];
        $this->priority = '';
        $this->customHeaders = [];
        $this->logText = '';
        $this->transport = $transport;
    }

    public function setTo($email, $name = '')
    {
        $this->toEmail = $email;
        $this->toName = $name;

        return $this;
    }

    public function getToName()
    {
        return $this->toName;
    }

    public function setFrom($email, $name = '')
    {
        $this->fromEmail = $email;
        $this->fromName = $name;

        return $this;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function setReplyTo($email, $name = false)
    {
        $this->replyEmail = $email;
        $this->replyName = $name;

        return $this;
    }

    public function getReplyToEmail()
    {
        return $this->replyEmail;
    }

    public function getReplyToName()
    {
        return $this->replyName;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getHTML()
    {
        return $this->html;
    }

    public function setHTML($html, $addAltText = false)
    {
        $this->html = $html;
        if ($addAltText) {
            $this->text = strip_tags($this->html);
        }

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $priority string Can be 'normal', 'urgent', or 'non-urgent'
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param mixed $messageId
     * @return Mailer
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function setXMailer($xMailer)
    {
        $this->xMailer = $xMailer;
        return $this;
    }

    public function attach($attachment, $inlineFileName = '')
    {
        $basename = $inlineFileName ?: basename($attachment);
        $this->attachments[$basename] = $attachment;
        $this->contentIds[$basename] = $this->genCid($basename);
        return $this;
    }

    private function genCid($name)
    {
        return md5($name);
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    public function setCustomHeaders(array $ch)
    {
        $this->customHeaders = $ch;

        return $this;
    }

    public function setDate(DateTimeInterface $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Sends the composed mail with the selected transport.
     *
     * @return boolean
     */
    public function send()
    {
        if (!$this->toEmail) {
            throw new InvalidArgumentException('Error: E-Mail to required!');
        }

        if (!$this->fromEmail) {
            throw new InvalidArgumentException('Error: E-Mail from required!');
        }

        if (!$this->subject) {
            throw new InvalidArgumentException('Error: E-Mail subject required!');
        }

        if (!$this->text && !$this->html) {
            throw new InvalidArgumentException('Error: E-Mail message required!');
        }

        $priorities = [Constant::PRIORITY_NORMAL, Constant::PRIORITY_URGENT, Constant::PRIORITY_NON_URGENT];

        if ($this->priority && !in_array($this->priority, $priorities, true)) {
            throw new InvalidArgumentException(
                "Priority possible values in " . join(', ', $priorities)
            );
        }

        $mime = $this->generateMime();

        return $this->transport->send($this->getFromEmail(), $this->getToEmail(), $mime['message'], $mime['headers']);
    }

    public function generateMime()
    {
        $eol = Constant::EOF;

        $headers = '';

        if ($this->messageId) {
            $headers .= 'MessagePart-ID: ' . $this->messageId . $eol;
        }

        if ($this->xMailer) {
            $headers .= 'X-Mailer: ' . $this->xMailer . $eol;
        }

        if (is_array($this->toEmail)) {
            $toEmails = [];
            foreach ($this->toEmail as $key => $value) {
                $toEmails[] = is_int($key) ? $this->formatEmail($value) : $this->formatEmail($key, $value);
            }
            $to = implode(', ', $toEmails);
        } elseif ($this->toName) {
            $to = $this->formatEmail($this->toEmail, $this->toName);
        } else {
            $to = $this->formatEmail($this->toEmail);
        }

        $toHeader = 'To: ' . $to . $eol;
        if ($this->hasNotUnicode($this->subject)) {
            $subject = $this->subject;
        } else {
            $subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';
        }

        $subjectHeader = 'Subject: ' . $subject . $eol;

        $message = '';

        $type = ($this->html && $this->text) ? 'alt' : 'plain';
        $type .= count((array)$this->attachments) ? '_attachments' : '';

        $headers .= 'MIME-Version: 1.0' . $eol;

        $from = $this->formatEmail($this->fromEmail, $this->fromName);

        $headers .= 'From: ' . $from . $eol;

        $replyTo = $from;
        if ($this->replyEmail) {
            $replyTo = $this->replyEmail;
            if ($this->replyName) {
                $replyTo = $this->formatEmail($this->replyEmail, $this->replyName);
            }
        }

        $headers .= 'Reply-To: ' . $replyTo . $eol;

        $date = $this->date ? $this->date : new DateTime();
        $headers .= 'Date: ' . gmdate('D, d M Y H:i:s O', $date->getTimestamp()) . $eol;

        if ($this->priority) {
            $headers .= 'Priority: ' . $this->priority . $eol;
        }

        if (count((array)$this->customHeaders)) {
            foreach ($this->customHeaders as $k => $v) {
                $headers .= $k . ': ' . $v . $eol;
            }
        }

        $boundary = '';

        switch ($type) {
            case 'alt':
            case 'plain_attachments':
            case 'alt_attachments':
                $boundary = $this->genBoundaryId();
                break;
        }

        switch ($type) {
            case 'plain':
                $headers .= 'Content-Type: ' . ($this->html ? 'text/html' : 'text/plain') . '; charset="UTF-8"';
                break;
            case 'alt':
                $headers .= 'Content-Type: multipart/alternative; format=flowed; delsp=yes; boundary="' .
                    $boundary . '"';
                break;
            case 'plain_attachments':
            case 'alt_attachments':
                $headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
                break;
        }

        switch ($type) {
            case 'plain':
                $message .= $this->html ?: $this->text;
                break;
            case 'alt':
                $message .= '--' . $boundary . $eol;
                $message .= $this->genEncodeTextPart($this->text);
                $message .= $eol . '--' . $boundary . $eol;
                $message .= $this->genEncodeHtmlPart($this->html);
                break;

            case 'plain_attachments':
                $message .= '--' . $boundary . $eol;
                if ($this->text) {
                    $message .= $this->genEncodeTextPart($this->text);
                } else {
                    $message .= $this->genEncodeHtmlPart($this->embedAttachments($this->html));
                }
                break;
            case 'alt_attachments':
                $boundary2 = 'bd2_' . $boundary;
                $message .= '--' . $boundary . $eol;
                $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . $eol . $eol;
                $message .= '--' . $boundary2 . $eol;
                $message .= $this->genEncodeTextPart($this->text);
                $message .= $eol . '--' . $boundary2 . $eol;
                $message .= $this->genEncodeHtmlPart($this->embedAttachments($this->html));
                $message .= $eol . '--' . $boundary2 . '--';
                break;
        }

        switch ($type) {
            case 'plain_attachments':
            case 'alt_attachments':
                foreach ($this->attachments as $basename => $fullname) {
                    $content = file_get_contents($fullname);
                    $message .= $eol . '--' . $boundary . $eol;
                    $message .= $this->genAttachHeaderPart($this->contentIds[$basename], $basename);
                    $message .= chunk_split(base64_encode($content), 76, $eol);
                }
                break;
        }

        switch ($type) {
            case 'alt':
            case 'plain_attachments':
            case 'alt_attachments':
                $message .= $eol . '--' . $boundary . '--';

                break;
        }

        $headers = $toHeader . $subjectHeader . $headers;

        return compact('from', 'to', 'headers', 'message');
    }

    private function formatEmail($email, $name = '')
    {
        $emailFormatted = '<' . $email . '>';

        if (!$name) {
            return $emailFormatted;
        }

        if ($this->hasNotUnicode($name)) {
            return $name . ' ' . $emailFormatted;
        }

        return '=?UTF-8?B?' . base64_encode($name) . '?= ' . $emailFormatted;
    }

    private function hasNotUnicode($str)
    {
        return preg_match('/^[a-zA-Z0-9\-\. ]+$/', $str);
    }

    protected function genBoundaryId()
    {
        return md5(uniqid(time(), true));
    }

    private function genEncodeTextPart($text)
    {
        $eol = Constant::EOF;
        $message = 'Content-Type: text/plain; charset="UTF-8"' . $eol;
        $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
        $message .= chunk_split(base64_encode($text), 76, $eol);
        return $message;
    }

    private function genEncodeHtmlPart($html)
    {
        $eol = Constant::EOF;
        $message = 'Content-Type: text/html; charset="UTF-8"' . $eol;
        $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
        $message .= chunk_split(base64_encode($html), 76, $eol);
        return $message;
    }

    private function embedAttachments($html)
    {
        $patterns = [];
        $replacements = [];

        foreach ($this->contentIds as $basename => $id) {
            $patterns[] = '/' . preg_quote($basename) . '/ui';
            $replacements[] = 'cid:' . $id;
        }

        return preg_replace($patterns, $replacements, $html);
    }

    private function genAttachHeaderPart($cid, $basename)
    {
        $eol = Constant::EOF;
        $message = 'Content-Type: application/octetstream' . $eol;
        $message .= 'Content-Transfer-Encoding: base64' . $eol;
        $message .= 'Content-Disposition: attachment; filename="' . $basename . '"' . $eol;
        $message .= 'Content-ID: <' . $cid . '>' . $eol . $eol;
        return $message;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function getToEmail()
    {
        return $this->toEmail;
    }
}
