<?php

// TODOs:
// 1. Add the sid field to the Message entity in the sms repo
// 2. Update the message's sid and status in the consumer.php script in sms-consumer
// 3. Test this script on a staging server

require __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use React\Http\Server;
use React\Http\Response;
use React\EventLoop\Factory;
use Psr\Http\Message\ServerRequestInterface;
use SmsTwilioWebhook\EntityManager;

$dotenv = new Dotenv(__DIR__.'/../');
$dotenv->load();

$em = (new EntityManager())->get();
$conn = $em->getConnection();

$loop = React\EventLoop\Factory::create();

$server = new Server(function (ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();
    if ($path === '/twilio-webhook') {
        if ($method === 'POST') {
            $body = $request->getParsedBody();
            $sid = $body['MessageSid'];
            $status = $body['MessageStatus'];
            $count = $conn->executeUpdate('UPDATE message SET status = ? WHERE sid = ?', [$status, $sid]);
            return new React\Http\Response(
                200,
                array('Content-Type' => 'text/plain'),
                'Delivery status tracked'.PHP_EOL
            );
        }
    }

    return new Response(404, ['Content-Type' => 'text/plain'],  'Not found');
});

$socket = new React\Socket\Server(getenv('TWILIO_WEBHOOK_IP').':'.getenv('TWILIO_WEBHOOK_PORT'), $loop);
$server->listen($socket);

echo 'Twilio webhook running at http://'.getenv('TWILIO_WEBHOOK_IP').':'.getenv('TWILIO_WEBHOOK_PORT').'/twilio-webhook'.PHP_EOL;

$loop->run();
