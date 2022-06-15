<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Database\Capsule\Manager as Capsule;

$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = static function ($msg) {
    switch($msg) {
        case 'last10':
            // Fetch and return 10 last timestamps
            Capsule::table('logging_of_timestamps')->latest('end')->take(10)->get();
            break;
        default:
            $start = new DateTime();

            echo ' [x] Waiting for ', $msg->body, " microseconds\n";
            usleep($msg->body);
        
            echo " [x] Inserting timestamps to database...\n";
            Capsule::table('logging_of_timestamps')->insert(['start' => $start->format('Y-m-d H:i:s'), 'end' => (new DateTime())->format('Y-m-d H:i:s')]);
        
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
