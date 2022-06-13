<?php

require "vendor/autoload.php";
//If you want the errors to be shown
error_reporting(E_ALL);

ini_set('display_errors', '1');
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
   "driver" => "mysql",
   'read' => [
      'host' => [
         '192.168.88.129',
         '192.168.88.130',
         '192.168.88.131',
      ],
   ],
   'write' => [
      'host' => '192.168.88.129',
   ],
   "database" => "bigdata",
   "username" => "root",
   "password" => "root"
]);
//Make this Capsule instance available globally.
$capsule->setAsGlobal();
// Setup the Eloquent ORM.
$capsule->bootEloquent();
