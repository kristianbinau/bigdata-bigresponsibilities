<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Setup connection to RabbitMQ
$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('task_queue', false, true, false, false);


// Create message.
$msg = new AMQPMessage(
    '',
    array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

// Send message.
$channel->basic_publish($msg, '', 'task_queue');

echo ' [x] Sent ', 'empty string', "\n";


// Close connections.
$channel->close();
$connection->close();
