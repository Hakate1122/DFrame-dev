<?php

use Datahihi1\RakNet\RakNetServer;
use DFrame\Command\Helper\ConsoleInput as Input;
use DFrame\Command\Helper\ConsoleOutput as Output;
use App\Chat\Chat;
use DFrame\Utils\Math\Pi;

$cli->register('hello', [\App\Command\Hello::class, 'handle']);
$cli->register('choice', [\App\Command\Hello::class, 'choice']);
$cli->register('hi', [\App\Command\Hello::class, 'num']);

$cli->register('sample', [\App\Command\Sample::class, 'handle']);
$cli->register('try-connect-sql', [\App\Command\Sample::class, 'tryConnectSQL']);
$cli->register('try-connect-db', [\App\Command\Sample::class, 'tryConnectDB']);

$cli->register('minesv:ping', function () {
    $host = Input::prompt('Enter host:', '127.0.0.1');
    $port = Input::prompt('Enter port:', '19132');
    $client = new \Datahihi1\RakNet\RakNetClient($host, $port);
    $response = $client->ping();
    if ($response !== null) {
        Output::success("Server is online!");
        Output::info(print_r($response, true));
    } else {
        Output::error("Server is offline or did not respond.");
    }
    $client->close();
});
$cli->register('minesv:run', function () {
    $motd = 'MCPE;Demo MOTD;2;0.2.0;0;20;1234567890';
    $port = '19132';
    $server = new RakNetServer($motd, $port);
    $server->run();
});

$cli->register('jsondb', [\App\Command\JsonDBCommand::class, 'handle']);

$cli->register('jsondb:server', function () {
    $options = getopt('', ['host:', 'port:']);
    $host = $options['host'] ?? '0.0.0.0';
    $port = isset($options['port']) ? (int)$options['port'] : 9501;

    // Start the JsonDB TCP server (this will block)
    $server = new \DFrame\JsonDB\Server($host, $port);
});

$cli->register('jsondb:client', function () {
    $options = getopt('', ['host:', 'port:']);
    $host = $options['host'] ?? '127.0.0.1';
    $port = isset($options['port']) ? (int)$options['port'] : 9501;

    $client = new \DFrame\JsonDB\Client($host, $port);

    echo "-- PING --\n";
    try {
        $ping = $client->ping();
        print_r($ping);
    } catch (Exception $e) {
        echo "Ping failed: " . $e->getMessage() . "\n";
    }

    echo "-- INSERT --\n";
    try {
        $inserted = $client->insert('users', ['name' => 'CLI Test', 'email' => 'cli@example.com']);
        print_r($inserted);
    } catch (Exception $e) {
        echo "Insert failed: " . $e->getMessage() . "\n";
    }

    echo "-- FIND --\n";
    try {
        $all = $client->find('users');
        print_r($all);
    } catch (Exception $e) {
        echo "Find failed: " . $e->getMessage() . "\n";
    }
});

$cli->register('send:mail', function () {
    try {
        $mail = new DFrame\Application\Mail();
        $mail->to(email: 'datd5400@gmail.com')
            ->subject(subject: 'Test Email from DFrame Mailer 2.0')
            ->body('This is a test email sent from DFrame Mailer 2.0.');
        $mail->send();
        echo cli_green("Email sent successfully.\n");
    } catch (Exception $e) {
        echo cli_red("Failed to send email: " . $e->getMessage() . "\n");
    }
});

$cli->register('benchmark:sort', [\App\Command\BenchmarkSort::class, 'handle']);

$cli->register('websocket:server', function () {

    $options = getopt('', ['host:', 'port:']);
    $host = $options['host'] ?? '0.0.0.0';
    $port = isset($options['port']) ? (int)$options['port'] : 9501;

    $chat = new Chat($host, $port);
    $chat->start();
});

$cli->register('math:pi', function () {
    echo "M_PI: " . Pi::default() . PHP_EOL;
    echo "Leibniz (100000 iters): " . Pi::leibniz(100000) . PHP_EOL;
    echo "High precision (1050 digits): " . Pi::highPrecision(1050) . PHP_EOL;
});
$cli->register('math:delta', function () {
    $a = (float) Input::prompt('Enter first number (a):', '10.5');
    $b = (float) Input::prompt('Enter second number (b):', '7.3');

    $absoluteDelta = DFrame\Utils\Math\Delta::absolute($a, $b);
    Output::info("Absolute difference between $a and $b is: $absoluteDelta");

    try {
        $relativeDelta = DFrame\Utils\Math\Delta::relative($a, $b);
        Output::info("Relative difference between $a and $b is: $relativeDelta%");
    } catch (\InvalidArgumentException $e) {
        Output::error($e->getMessage());
    }
});
