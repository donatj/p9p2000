<?php

require __DIR__ . '/../vendor/autoload.php';

use donatj\P9p2000\StreamWrapper;

StreamWrapper::register();

$file = '9p://localhost:9999/example-' . bin2hex(random_bytes(8)) . '.txt';

if( file_put_contents($file, "Hello, 9P2000!\n") === false ) {
	throw new RuntimeException('Failed to write the example file');
}

$contents = file_get_contents($file);
if( $contents === false ) {
	throw new RuntimeException('Failed to read the example file');
}

echo $contents;
