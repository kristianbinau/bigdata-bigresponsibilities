<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    switch($msg) {
        case '':
            // Fetch and return 10 last timestamps
            break;
        default:
            $start = time();

            echo ' [x] Waiting for ', $msg->body, " seconds\n";
            sleep($msg->body);
        
            echo " [x] Inserting timestamps to database...\n";
            Capsule::table('logging_of_users')->insert(['start' => $start, 'end' => time()]);
        
            echo " [x] Done\n";
            $msg->ack();
            break;
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
