<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


pcntl_signal(SIGINT,"shutdown");

$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

function shutdown(){
    global $connection, $channel;
    $channel->close();
    $connection->close();
};


while(true) {
    $data = rand(0, 10) / 10;
    $msg = new AMQPMessage(
        $data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );
    
    $channel->basic_publish($msg, '', 'task_queue');
    
    echo ' [x] Sent ', $data, "\n";

    sleep(0.3);
}



