<?php

require_once __DIR__ . '/start.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Database\Capsule\Manager as Capsule;

$connection = new AMQPStreamConnection('192.168.88.128', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

/**
 * Checks if a number is prime
 *
 * @param $num
 * @return bool
 */
function IsPrime($num): bool
{
    if ($num < 2) {
        return false;
    }
    for ($i = 2; $i <= $num / 2; $i++) {
        if ($num % $i == 0) {
            return false;
        }
    }

    return true;
}

/**
 * Recursively solves The Fibonacci Sequence
 * Max is the highest allowed value to be returned.
 *
 * @param int $max
 * @param array $previous
 * @param int $actual
 * @return int[]
 * @see https://en.wikipedia.org/wiki/Fibonacci_number
 */
function fibonacciThis(int $max, array $previous = [0], int $actual = 0): array
{
    $maxIntSize = PHP_INT_SIZE === 4 ? 1073741823 : 4611686000000000000;

    if ($max > $maxIntSize) {
        return fibonacciThis($maxIntSize, $previous, $actual);
    }

    $result = end($previous) + $actual;
    $previous[] = $actual;

    if ($result > $max) {
        return $previous;
    }

    return fibonacciThis($max, $previous, $result?:1);
}

$callback = static function ($msg) {
    switch($msg) {
        case 'last10':
            // Fetch and return 10 last timestamps
            Capsule::table('logging_of_timestamps')->latest('end')->take(10)->get();
            break;
        case 'fibonacci':
            fibonacciThis(4611686000000000000);
            break;
        case 'prime':
            isPrime(99194853094755497);
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
