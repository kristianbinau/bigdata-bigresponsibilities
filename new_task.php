<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Setup connection to RabbitMQ
$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('task_queue', false, true, false, false);

/**
 * Run 1000 times.
 * Send a value between 0 and 1000000.
 */
for($i = 0 ; $i < 10000 ; $i++) {
    try {
        $data = random_int(0, 10000);
    } catch (Exception $e) {
        echo ' Appropriate source of randomness cannot be found, using Mersenne Twister instead.', "\n";
        $data = mt_rand(0, 10000);
    }

    // Create message.
    $msg = new AMQPMessage(
        $data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );
    
    // Send message.
    $channel->basic_publish($msg, '', 'task_queue');
    
    echo ' [x] Sent ', $data, "\n";
}

// Close connections.
$channel->close();
$connection->close();
