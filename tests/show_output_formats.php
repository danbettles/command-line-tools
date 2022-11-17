<?php

use DanBettles\CommandLineTools\Output;

require __DIR__ . '/../vendor/autoload.php';

$output = new Output();

foreach (['writeLine', 'command', 'danger', 'info'] as $methodName) {
    $output->{$methodName}("{$methodName}()");
}
