<?php

use SmsTwilioWebhook\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__.'/../vendor/autoload.php';

$em = (new EntityManager())->get();

return ConsoleRunner::createHelperSet($em);
