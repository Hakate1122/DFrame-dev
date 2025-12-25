<?php

use DFrame\Command\Helper\ConsoleInput;
use DFrame\Command\Helper\ConsoleOutput;

$cli->register('hello', [\App\Command\Hello::class, 'handle']);
$cli->register('choice', [\App\Command\Hello::class, 'choice']);
$cli->register('hi', [\App\Command\Hello::class, 'num']);

$cli->register('sample', [\App\Command\Sample::class, 'handle']);
$cli->register('try-connect-sql', [\App\Command\Sample::class, 'tryConnectSQL']);
$cli->register('try-connect-db', [\App\Command\Sample::class, 'tryConnectDB']);

$cli->register('demo', function () {
    $host = ConsoleInput::promptSecret('Enter host:', 'play.cubecraft.net');
    $port = ConsoleInput::promptSecret('Enter port:', '19132');
    $client = new \Datahihi1\RakNet\RakNetClient($host, $port);
    $response = $client->ping();
    if ($response !== null) {
        ConsoleOutput::success("Server is online!");
        ConsoleOutput::info(json_encode($response, JSON_PRETTY_PRINT));
    } else {
        ConsoleOutput::error("Server is offline or did not respond.");
    }
    $client->close();
});

$cli->register('jsondb', function () {
    try {
        $dbPath = getcwd() . DIRECTORY_SEPARATOR . 'users.json';
        $db = new DFrame\JsonDB\JsonDB($dbPath);

        while (true) {
            $choice = ConsoleInput::select(
                "Choose an action",
                [
                    "1" => "View All Records",
                    "2" => "Add New Record",
                    "3" => "Search Records",
                    "4" => "Update Record",
                    "5" => "Delete Record",
                    "6" => "Exit",
                ],
                "6"
            );

            switch ($choice) {
                case "1": // View All
                    $all = $db->all();
                    if (empty($all)) {
                        ConsoleOutput::info("No records found.");
                    } else {
                        foreach ($all as $row) {
                            ConsoleOutput::info(json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
                        }
                    }
                    break;

                case "2": // Add
                    $name = ConsoleInput::prompt("Name", null, function ($v) {
                        return $v !== '' ? true : "Name is required.";
                    });

                    $email = ConsoleInput::prompt("Email", null, ConsoleInput::validateEmail());

                    $age = ConsoleInput::prompt("Age", null, ConsoleInput::validateNumber());

                    $newId = $db->insert([
                        'name' => $name,
                        'email' => $email,
                        'age' => (int) $age,
                    ]);

                    ConsoleOutput::success("Inserted record with ID: {$newId}");
                    break;

                case "3": // Search
                    $by = ConsoleInput::select("Search by:", [
                        'id' => 'ID',
                        'name' => 'Name',
                        'email' => 'Email',
                    ], 'name');

                    if ($by === 'id') {
                        $id = (int) ConsoleInput::prompt('ID', null, ConsoleInput::validateNumber());
                        $rec = $db->find($id);
                        if ($rec === null) {
                            ConsoleOutput::info("Record not found for ID {$id}.");
                        } else {
                            ConsoleOutput::ok(json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
                        }
                    } else {
                        $val = ConsoleInput::prompt('Value to search');
                        $results = $db->where($by, $val);
                        if (empty($results)) {
                            ConsoleOutput::info("No matching records.");
                        } else {
                            foreach ($results as $r) {
                                echo json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
                            }
                        }
                    }
                    break;

                case "4": // Update
                    $id = (int) ConsoleInput::prompt('ID to update', null, ConsoleInput::validateNumber());
                    $existing = $db->find($id);
                    if ($existing === null) {
                        ConsoleOutput::error("Record with ID {$id} not found.");
                        break;
                    }

                    $name = ConsoleInput::prompt('Name', $existing['name'] ?? null);
                    $email = ConsoleInput::prompt('Email', $existing['email'] ?? null, ConsoleInput::validateEmail());
                    $age = ConsoleInput::prompt('Age', isset($existing['age']) ? (string)$existing['age'] : null, ConsoleInput::validateNumber());

                    $ok = $db->update($id, [
                        'name' => $name,
                        'email' => $email,
                        'age' => (int) $age,
                    ]);

                    if ($ok) {
                        ConsoleOutput::success("Record {$id} updated.");
                    } else {
                        ConsoleOutput::error("Failed to update record {$id}.");
                    }
                    break;

                case "5": // Delete
                    $id = (int) ConsoleInput::prompt('ID to delete', null, ConsoleInput::validateNumber());
                    $confirm = ConsoleInput::askYesNo("Are you sure you want to delete ID {$id}?", false);
                    if ($confirm) {
                        $deleted = $db->delete($id);
                        if ($deleted) ConsoleOutput::success("Record {$id} deleted.");
                        else ConsoleOutput::error("Failed to delete record {$id}.");
                    } else {
                        ConsoleOutput::info("Delete cancelled.");
                    }
                    break;

                case "6": // Exit
                    ConsoleOutput::info("Exiting jsondb CLI.");
                    return;

                default:
                    ConsoleOutput::error("Unknown option selected.");
                    break;
            }
        }

    } catch (Exception $e) {
        echo "Lỗi: " . $e->getMessage();
    }
});
