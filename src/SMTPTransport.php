<?php

namespace Ouranoshong\Mailer;

/**
 * Class SMTPTransport
 */
class SMTPTransport implements TransportInterface
{

    /**
     * smtp transport parameter
     *
     * @var array
     */
    public $transport = [];

    /**
     * Debug Mode
     *
     * @var boolean|integer
     */
    public $debugMode = false;

    /**
     * Debug log text
     *
     * @var string
     */
    private $logText;

    /**
     * Allowed encryptions
     *
     * @var array
     */
    private $allowedEncryptions = [
        'ssl',
        'tls',
    ];

    /**
     * SMTPTransport constructor.
     * @param array $opt
     */
    public function __construct(array $opt = [])
    {
        $default = [
            'host' => 'localhost',
            'username' => '',
            'password' => '',
            'port' => 25,
            'encryption' => '',
            'starttls' => false,
            'httpProxy' => '',
            'context' => []
        ];

        $this->transport = array_merge($default, $opt);

        if (false === in_array($this->transport['encryption'], $this->allowedEncryptions)) {
            $this->transport['encryption'] = '';
        }
    }

    /**
     * @param $fromEmail string
     * @param $toEmail mixed
     * @param $message string
     * @param null $headers
     * @return bool
     */
    public function send($fromEmail, $toEmail, $message, $headers = null)
    {
        $eof = Constant::EOF;
        $this->logText = null;
        $socket = null;

        try {
            $socket = $this->connect();

            $this->serverParse($socket, '220');
            $this->socketSend($socket, 'EHLO ' . $this->transport['host'] . $eof);
            $this->serverParse($socket, '250');

            $this->startTls($socket);
            $this->auth($socket);

            $this->socketSend($socket, "MAIL FROM:<{$fromEmail}>" . $eof);
            $this->serverParse($socket, '250');

            $to = is_string($toEmail) ? [$toEmail] : $toEmail;
            foreach ($to as $key => $value) {
                $email = is_string($key) ? $key : $value;
                $this->socketSend($socket, "RCPT TO:<{$email}>" . $eof);
                $this->serverParse($socket, '250');
            }

            $this->socketSend($socket, 'DATA' . $eof);
            $this->serverParse($socket, '354');
            $this->socketSend($socket, trim($headers) . $eof . $eof . trim($message) . $eof);
            $this->socketSend($socket, '.' . $eof);
            $this->serverParse($socket, '250');
            $this->socketSend($socket, 'QUIT' . $eof);
            fclose($socket);
        } catch (\Exception $e) {
            if (is_resource($socket)) {
                fclose($socket);
            }
            if ($this->debugMode) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            return false;
        }

        return true;
    }

    /**
     * @return false|resource
     */
    protected function connect()
    {
        $socket = null;

        if (isset($this->transport['httpProxy']) === true && ($this->transport['httpProxy']) !== '') {
            $socket = $this->connectHttpProxy();
        }

        if (($socket !== null) && $this->transport['encryption'] && !$this->transport['starttls']) {
            if (false === stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)) {
                throw new Exception('Unable to ' . $this->transport['encryption'] . ' encryption', 500);
            }
        }

        if (null !== $socket) {
            return $socket;
        }

        return $this->connectDirectly();
    }

    private function connectHttpProxy()
    {
        $host = parse_url($this->transport['httpProxy'], PHP_URL_HOST);
        $port = parse_url($this->transport['httpProxy'], PHP_URL_PORT);
        $user = parse_url($this->transport['httpProxy'], PHP_URL_USER);
        $pass = parse_url($this->transport['httpProxy'], PHP_URL_PASS);
        $creds = '';
        if (($user !== false) && ($pass !== false)) {
            $creds = $user . ':' . $pass;
        }

        $destination = $this->transport['host'] . ':' . $this->transport['port'];

        $sslContext = [];
        if ($this->transport['encryption']) {
            $sslContext = array_merge(['ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]], $this->transport['context']);
        }

        $context = stream_context_create($sslContext);

        $socket = stream_socket_client(
            'tcp://' . $host . ':' . $port,
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($errno == 0) {
            $auth = $creds ? 'Proxy-Authorization: Basic ' . base64_encode($creds) . "\r\n" : '';
            $connect = "CONNECT $destination HTTP/1.1\r\n$auth\r\n";
            fwrite($socket, $connect);
            $rsp = fread($socket, 1024);
            if (preg_match('/^HTTP\/\d\.\d 200/', $rsp) == 1) {
                return $socket;
            }

            throw new Exception("Request denied, $rsp\n");
        }

        throw new Exception('Connect Proxy Server ' . $host . ':' . $port . ' failed: ' . $errno . ' ' . $errstr);
    }

    private function connectDirectly()
    {
        $protocol = '';

        if ($this->transport['encryption'] === 'ssl') {
            $protocol = 'ssl://';
        }

        $uri = $protocol . $this->transport['host'] . ':' . $this->transport['port'];

        $context = stream_context_create($this->transport['context']);

        $socket = stream_socket_client(
            $uri,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            throw new Exception(
                sprintf("Error connecting to '%s' (%s) (%s)", $uri, $errno, $errstr),
                500
            );
        }

        return $socket;
    }

    /**
     * Server Response Parser
     *
     * @param resource $socket fsockopen resource
     * @param string $expectedResponse
     * @return void
     */
    protected function serverParse($socket, $expectedResponse)
    {
        $serverResponse = '';

        while (substr($serverResponse, 3, 1) != ' ') {
            if (!($serverResponse = fgets($socket, 256))) {
                throw new Exception('Error while fetching server response codes.' . __FILE__ . __LINE__, 500);
            }

            $this->logText .= date('Y-m-d h:i:s') . ' SERVER -> CLIENT: ' . trim($serverResponse) . "\n";
        }

        if (!(substr($serverResponse, 0, 3) == $expectedResponse)) {
            throw new Exception("Unable to send e-mail.{$serverResponse}" . __FILE__ . __LINE__, 500);
        }
    }

    protected function socketSend($socket, $message)
    {
        $this->logText .= date('Y-m-d h:i:s') . ' CLIENT -> SERVER: ' . $message;

        fwrite($socket, $message);
    }

    private function startTls($socket)
    {
        $eof = Constant::EOF;

        if (($this->transport['encryption'] === 'tls') && $this->transport['starttls']) {
            $this->socketSend($socket, 'STARTTLS' . $eof);
            $this->serverParse($socket, '220');

            if (false === stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Unable to start tls encryption', 500);
            }

            $this->socketSend($socket, 'EHLO ' . $this->transport['host'] . $eof);
            $this->serverParse($socket, '250');
        }
    }

    private function auth($socket)
    {
        $eof = Constant::EOF;

        if ($this->transport['username'] && $this->transport['password']) {
            $this->socketSend($socket, 'AUTH LOGIN' . $eof);
            $this->serverParse($socket, '334');
            $this->socketSend($socket, base64_encode($this->transport['username']) . $eof);
            $this->serverParse($socket, '334');
            $this->socketSend($socket, base64_encode($this->transport['password']) . $eof);
            $this->serverParse($socket, '235');
        }
    }
}
