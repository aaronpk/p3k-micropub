<?php
require('vendor/autoload.php');

$request = p3k\Micropub\Request::createFromPostArray([
  'h' => 'entry'
]);

if($request->error) {
  echo "Error: ".$request->error_description."\n";
}

if(get_class($request) == \p3k\Micropub\Error::class) {
  echo "Error: ".$request->error_description."\n";
}

