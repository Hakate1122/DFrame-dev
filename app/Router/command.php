<?php

// $cli được truyền từ dli

$cli->register('hello', function () {
    echo "Hello from custom command!\n";
});

$cli->register('sample', function () {
    echo "Sample custom logic.\n";
});
