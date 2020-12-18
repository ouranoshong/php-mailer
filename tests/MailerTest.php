<?php

namespace Ouranoshong\Tests\Mailer;

use DateTime;
use Ouranoshong\Mailer\Constant;
use Ouranoshong\Mailer\Mailer;
use Ouranoshong\Mailer\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class MailerTest
 * @package Ouranoshong\Tests
 * @covers \Ouranoshong\Mailer\Mailer
 */
class MailerTest extends TestCase
{
    public function testSend()
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com', 'from');
        $mailer->setTo('to@example.com', 'to');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $this->assertTrue($mailer->send());
    }

    public function testSendWithoutFrom()
    {
        $this->expectExceptionMessage('Error: E-Mail from required!');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $this->assertTrue($mailer->send());
    }

    public function testSendWithoutTo()
    {
        $this->expectExceptionMessage('Error: E-Mail to required!');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $this->assertTrue($mailer->send());
    }

    public function testSendWithoutSubject()
    {
        $this->expectExceptionMessage('Error: E-Mail subject required!');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('');
        $mailer->setText('text');
        $this->assertTrue($mailer->send());
    }

    public function testSendWithoutText()
    {
        $this->expectExceptionMessage('Error: E-Mail message required!');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('');
        $this->assertTrue($mailer->send());
    }

    public function testSendWithWrongPriority()
    {
        $this->expectExceptionMessage('Priority possible values in');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send')->willReturn(true);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $mailer->setPriority('test');
        $this->assertTrue($mailer->send());
    }

    public function testGenerateMime()
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));

        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/text.txt'),
            $mime['headers']
        );

        $this->assertEquals('text', $mime['message']);
    }

    public function testGenerateMimeWithHtml()
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setHTML('<p>html</p>');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/html.txt'),
            $mime['headers']
        );

        $this->assertEquals('<p>html</p>', $mime['message']);
    }

    public function testGenerateMimeWithAttachments()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('1234');
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('text');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg');
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/attach.txt'),
            $mime['headers'] . $mime['message']
        );
    }

    public function testGenerateMimeWithEmbedAttachments()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('1234');
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setHTML('<p>html image <img src="test.jpeg" /></p>');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpeg');
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/embed_attach.txt'),
            $mime['headers'] . $mime['message']
        );
    }

    public function testGenerateMimeWithAltEmbedAttachments()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('1234');
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setHTML('<p>html image <img src="test.jpeg" /></p>', true);
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpeg');
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/alt_embed_attach.txt'),
            $mime['headers'] . $mime['message']
        );
    }

    public function testGenerateMimeWithTextAttachments()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('1234');
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setText('<p>html image <img src="test.jpeg" /></p>');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpeg');
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/text_attach.txt'),
            $mime['headers'] . $mime['message']
        );
    }

    public function testGenerateMimeWithAtlMessage()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('1234');
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setFrom('from@example.com');
        $mailer->setTo('to@example.com');
        $mailer->setSubject('subject');
        $mailer->setHTML('<p>html image <img src="test.jpeg" /></p>', true);
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 06:26:07 +0000'));
        $mime = $mailer->generateMime();

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/alt_html.txt'),
            $mime['headers'] . $mime['message']
        );
    }

    public function testFullHeaders()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('d6cac6b0ea5c7b995176f68e6384201f');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 10:29:32 +0000'));
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setSubject('subject');
        $mailer->setHTML('<p>html</p>', true);
        $mailer->setFrom('from@example.com', 'from');
        $mailer->setTo(['to@example.com' => 'to', 'to1@example.com']);
        $mailer->setMessageId('1234567');
        $mailer->setPriority(Constant::PRIORITY_NORMAL);
        $mailer->setCustomHeaders(['X-Test' => 'test']);
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpg');
        $mailer->setReplyTo('from@example.com', 'from');

        $this->assertEquals(array (
            'X-Test' => 'test'
        ), $mailer->getCustomHeaders());
        $this->assertEquals('subject', $mailer->getSubject());
        $this->assertEquals('<p>html</p>', $mailer->getHTML());
        $this->assertEquals('html', $mailer->getText());
        $this->assertEquals(array (
            'test.jpg' => __DIR__ .'/fixtures/test.jpeg'
        ), $mailer->getAttachments());
        $this->assertEquals('from@example.com', $mailer->getFromEmail());
        $this->assertEquals('from', $mailer->getFromName());
        $this->assertEquals(array (
            'to1@example.com',
            'to@example.com' => 'to'
        ), $mailer->getToEmail());
        $this->assertEquals(Constant::PRIORITY_NORMAL, $mailer->getPriority());
        $this->assertEquals('from@example.com', $mailer->getReplyToEmail());
        $this->assertEquals('from', $mailer->getReplyToName());

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/all_headers.txt'),
            $mailer->generateMime()['headers']
        );
    }

    public function testToEmail()
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setTo('to@example.com', 'to');
        $this->assertEquals('to@example.com', $mailer->getToEmail());
        $this->assertEquals('to', $mailer->getToName());
    }

    public function testUnicodeString()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('d6cac6b0ea5c7b995176f68e6384201f');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 10:29:32 +0000'));
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setSubject('邮件标题');
        $mailer->setHTML('<p>html</p>', true);
        $mailer->setFrom('from@example.com', '发件人');
        $mailer->setTo('to@example.com', '收件人');
        $mailer->setMessageId('1234567');
        $mailer->setPriority(Constant::PRIORITY_NORMAL);
        $mailer->setCustomHeaders(['X-Test' => 'test']);
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpg');
        $mailer->setReplyTo('from@example.com', '回复人');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/unicode_headers.txt'),
            $mailer->generateMime()['headers']
        );
    }

    public function testUnicodeStringWithNoReplyName()
    {
        $mailer = $this->createPartialMock(Mailer::class, ['genBoundaryId']);
        $mailer->method('genBoundaryId')->willReturn('d6cac6b0ea5c7b995176f68e6384201f');
        $mailer->setDate(new DateTime('Tue, 30 Jun 2020 10:29:32 +0000'));
        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setSubject('邮件标题');
        $mailer->setHTML('<p>html</p>', true);
        $mailer->setFrom('from@example.com', '发件人');
        $mailer->setTo('to@example.com', '收件人');
        $mailer->setMessageId('1234567');
        $mailer->setPriority(Constant::PRIORITY_NORMAL);
        $mailer->setCustomHeaders(['X-Test' => 'test']);
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpg');
        $mailer->setReplyTo('from@example.com');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/fixtures/no_reply_to_name.txt'),
            $mailer->generateMime()['headers']
        );
    }


    public function testGenBoundary()
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);

        $mailer->setXMailer('PHP/7.4.7');
        $mailer->setSubject('邮件标题');
        $mailer->setHTML('<p>html</p>', true);
        $mailer->setFrom('from@example.com', '发件人');
        $mailer->setTo('to@example.com', '收件人');
        $mailer->setMessageId('1234567');
        $mailer->setPriority(Constant::PRIORITY_NORMAL);
        $mailer->setCustomHeaders(['X-Test' => 'test']);
        $mailer->attach(__DIR__ . '/fixtures/test.jpeg', 'test.jpg');
        $mailer->setReplyTo('from@example.com');

        $this->assertTrue(
            preg_match(
                '/Date: \w+/',
                $mailer->generateMime()['headers']
            )
        );

        $this->assertTrue(
            preg_match(
                '/boundary="\w{32}"/',
                $mailer->generateMime()['headers']
            )
        );
    }
}
