<?php


namespace Ouranoshong\Tests\Mailer;


use Ouranoshong\Mailer\Constant;
use Ouranoshong\Mailer\Exception;
use Ouranoshong\Mailer\SMTPTransport;
use PHPUnit\Framework\TestCase;

/**
 * Class SMTPTransportTest
 * @package Ouranoshong\Tests\Mailer
 * @covers \Ouranoshong\Mailer\SMTPTransport
 */
class SMTPTransportTest extends TestCase
{

    protected function getSTMPConfig()
    {
        return [
            'host' => '127.0.0.1',
            'port' => 25,
            'username' => 'username',
            'password' => 'password'
        ];
    }

    protected function getHeaders()
    {
        return file_get_contents(__DIR__ . '/fixtures/mail_headers.txt');
    }

    protected function getMessage()
    {
        return file_get_contents(__DIR__ . '/fixtures/mail_message.txt');
    }

    public function testConnectDirectly()
    {
        $transport = new SMTPTransport(
            $this->getSTMPConfig()
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectDirectlySSL()
    {
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'port' => 465,
                    'encryption' => 'ssl',
                    'context' => [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'local_cert' => __DIR__ . '/server/server.pem',
                            'allow_self_signed' => true,
                        ],
                    ]
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectDirectlyStarttls()
    {
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'port' => 587,
                    'starttls' => true,
                    'encryption' => 'tls',
                    'context' => [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'local_cert' => __DIR__ . '/server/server.pem',
                            'allow_self_signed' => true,
                        ],
                    ]
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectWithProxy()
    {
        $transport = new SMTPTransport(
            array_merge($this->getSTMPConfig(), ['httpProxy' => 'http://127.0.0.1:9999'])
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectSSLWithProxy()
    {
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'port' => 465,
                    'encryption' => 'ssl',
                    'context' => [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'local_cert' => __DIR__ . '/server/server.pem',
                            'allow_self_signed' => true,
                        ],
                    ],
                    'httpProxy' => 'http://127.0.0.1:9999'
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectDirectlyOnNoSslPort()
    {
        $this->expectException(Exception::class);
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'starttls' => true,
                    'encryption' => 'tls'
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectNotSslWithProxy()
    {
        $this->expectException(Exception::class);
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'encryption' => 'ssl',
                    'httpProxy' => 'http://127.0.0.1:9999'
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectErrorProxy()
    {
        $this->expectException(Exception::class);
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'httpProxy' => 'http://127.0.0.1:9998'
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }

    public function testConnectErrorPort()
    {
        $this->expectException(Exception::class);
        $transport = new SMTPTransport(
            array_merge(
                $this->getSTMPConfig(),
                [
                    'port' => 1234
                ]
            )
        );
        $transport->debugMode = Constant::DEBUG_ON;
        $this->assertTrue(
            $transport->send(
                'from@example.com',
                'to@example.com',
                $this->getMessage(),
                $this->getHeaders()
            )
        );
    }
}
