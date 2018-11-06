<?php

require __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use React\Http\Server;
use React\Http\Response;
use React\EventLoop\Factory;
use Psr\Http\Message\ServerRequestInterface;

$dotenv = new Dotenv(__DIR__.'/../');
$dotenv->load();

$loop = React\EventLoop\Factory::create();

$server = new Server(function (ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();
    if ($path === '/twilio-webhook') {
        if ($method === 'POST') {
            // TODO
            // Update the message table
            // Track the delivery status of the message as in https://www.twilio.com/docs/sms/tutorials/how-to-confirm-delivery-php
            return new React\Http\Response(
                200,
                array('Content-Type' => 'text/plain'),
                'Delivery status successfully tracked!'.PHP_EOL
            );
        }
    }

    return new Response(404, ['Content-Type' => 'text/plain'],  'Not found');
});

$socket = new React\Socket\Server(getenv('TWILIO_WEBHOOK_IP').':'.getenv('TWILIO_WEBHOOK_PORT'), $loop);
$server->listen($socket);

echo 'Twilio webhook running at http://'.getenv('TWILIO_WEBHOOK_IP').':'.getenv('TWILIO_WEBHOOK_PORT').'/twilio-webhook'.PHP_EOL;

$loop->run();
