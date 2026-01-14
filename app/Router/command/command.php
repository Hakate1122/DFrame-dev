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

$cli->register('send:mail', function () {
    try{
    $mail = new DFrame\Application\Mail();
    $mail   ->to(email: 'datd5400@gmail.com')
            ->subject(subject: 'Test Email from DFrame Mailer 2.0')
            ->body('This is a test email sent from DFrame Mailer 2.0.');
    $mail->send();
    echo cli_green("Email sent successfully.\n");
    }
    catch(Exception $e){
        echo cli_red("Failed to send email: " . $e->getMessage() . "\n");
    }
});
$cli->register('benchmark:sort', [\App\Command\BenchmarkSort::class, 'handle']);

$cli->register('websocket:server', function () {

    // Parse command line arguments
    $options = getopt('', ['host:', 'port:']);
    $host = $options['host'] ?? '0.0.0.0';
    $port = isset($options['port']) ? (int)$options['port'] : 9501;

    // Create and start the WebSocket server
    $chat = new Chat($host, $port);
    $chat->start();

});

$cli->register('math:pi', function () {
echo "M_PI: " . Pi::default() . PHP_EOL;
echo "Leibniz (100000 iters): " . Pi::leibniz(100000) . PHP_EOL;
echo "High precision (1050 digits): " . Pi::highPrecision(1050) . PHP_EOL;
});
