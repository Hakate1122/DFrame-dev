<?php

namespace App\Command;

use DFrame\Command\Helper\ConsoleInput as Input;
use DFrame\Command\Helper\ConsoleOutput as Output;
use DFrame\JsonDB\JsonDB;

class JsonDBCommand
{
    public static function handle()
    {
        try {
            $dbPath = getcwd() . DIRECTORY_SEPARATOR . 'users.json';
            $db = new JsonDB($dbPath);

            while (true) {
                $choice = Input::select(
                    "Choose an action",
                    [
                        "1" => "View All Records",
                        "2" => "Add New Record",
                        "3" => "Search Records",
                        "4" => "Update Record",
                        "5" => "Delete Record",
                        "6" => "Exit",
                        "7" => "Clear Screen",
                    ],
                    "6"
                );

                switch ($choice) {
                    case "1": // View All
                        $all = $db->all();
                        if (empty($all)) {
                            Output::info("No records found.");
                        } else {
                            foreach ($all as $row) {
                                Output::info(json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
                            }
                            // Wait for user to press Enter to continue
                            Input::prompt("Press Enter to continue...", null, function () {
                                return true;
                            });
                        }
                        break;

                    case "2": // Add
                        $name = Input::prompt("Name", null, function ($v) {
                            return $v !== '' ? true : "Name is required.";
                        });

                        $email = Input::prompt("Email", null, Input::validateEmail());
                        $age = Input::prompt("Age", null, Input::validateNumber());

                        $newId = $db->insert([
                            'name' => $name,
                            'email' => $email,
                            'age' => (int) $age,
                        ]);

                        Output::success("Inserted record with ID: {$newId}");
                        break;

                    case "3": // Search
                        $by = Input::select("Search by:", [
                            'id' => 'ID',
                            'name' => 'Name',
                            'email' => 'Email',
                        ], 'name');

                        if ($by === 'id') {
                            $id = (int) Input::prompt('ID', null, Input::validateNumber());
                            $rec = $db->find($id);
                            if ($rec === null) {
                                Output::info("Record not found for ID {$id}.");
                            } else {
                                Output::ok(json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
                            }
                        } else {
                            $val = Input::prompt('Value to search');
                            $results = $db->where($by, $val);
                            if (empty($results)) {
                                Output::info("No matching records.");
                            } else {
                                foreach ($results as $r) {
                                    echo json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
                                }
                            }
                        }
                        break;

                    case "4": // Update
                        $id = (int) Input::prompt('ID to update', null, Input::validateNumber());
                        $existing = $db->find($id);
                        if ($existing === null) {
                            Output::error("Record with ID {$id} not found.");
                            break;
                        }

                        $name = Input::prompt('Name', $existing['name'] ?? null);
                        $email = Input::prompt('Email', $existing['email'] ?? null, Input::validateEmail());
                        $age = Input::prompt('Age', isset($existing['age']) ? (string)$existing['age'] : null, Input::validateNumber());

                        $ok = $db->update($id, [
                            'name' => $name,
                            'email' => $email,
                            'age' => (int) $age,
                        ]);

                        if ($ok) {
                            Output::success("Record {$id} updated.");
                        } else {
                            Output::error("Failed to update record {$id}.");
                        }
                        break;

                    case "5": // Delete
                        $id = (int) Input::prompt('ID to delete', null, Input::validateNumber());
                        $confirm = Input::askYesNo("Are you sure you want to delete ID {$id}?", false);
                        if ($confirm) {
                            $deleted = $db->delete($id);
                            if ($deleted) Output::success("Record {$id} deleted.");
                            else Output::error("Failed to delete record {$id}.");
                        } else {
                            Output::info("Delete cancelled.");
                        }
                        break;

                    case "6": // Exit
                        Output::info("Exiting jsondb CLI.");
                        return;

                    case "7": // Clear Screen
                        // Attempt to clear screen in a robust way
                        if (ob_get_level()) {
                            ob_end_flush();
                        }
                        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                            system('cls');
                        } else {
                            system('clear');
                        }
                        // Fallback: print newlines if clear did not work
                        echo str_repeat(PHP_EOL, 40);
                        if (ob_get_level()) {
                            ob_flush();
                        }
                        break;

                    default:
                        Output::error("Unknown option selected.");
                        break;
                }
            }
        } catch (\Exception $e) {
            echo "Lỗi: " . $e->getMessage();
        }
    }
}
