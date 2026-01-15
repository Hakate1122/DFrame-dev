<?php
namespace App\Chat;

use DFrame\Application\WebSocket;

class Chat extends WebSocket{
    protected function onOpen(\Socket $client): void
    {
        $this->sendMessageToAll("A new user has joined the chat.");
    }

    protected function onClose(\Socket $client): void
    {
        $this->sendMessageToAll("A user has left the chat.");
    }

    protected function onMessage(\Socket $client, string $message): void
    {
        $this->sendMessageToAll($message, $client);
    }

    private function sendMessageToAll(string $message, ?\Socket $excludeClient = null): void
    {
        $excludeId = $excludeClient ? spl_object_id($excludeClient) : null;
        
        foreach ($this->clients as $client) {
            $clientId = spl_object_id($client);
            if ($clientId !== $excludeId) {
                $this->send($client, $message);
            }
        }
    }
}