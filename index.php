<?php

require "vendor/autoload.php";

use Discord\Discord;
use Discord\WebSockets\Event;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discordToken = $_ENV["DISCORD_BOT_TOKEN"];
$channelId = $_ENV["DISCORD_CHANNEL_ID"];

$discord = new Discord([
    "token" => $discordToken,
]);

$discord->on("ready", function ($discord) use ($channelId) {
    echo "Bot está pronto.", PHP_EOL;

    $commandPrefix = "!";
    $messageCount = 0;

    $discord->on(Event::MESSAGE_CREATE, function ($message) use (
        $channelId,
        $commandPrefix,
        $discord,
        $messageCount
    ) {
        if (strpos($message->content, $commandPrefix) === 0) {
            $command = substr($message->content, strlen($commandPrefix));

            if ($command === "delete") {
                if ($message->channel_id === $channelId) {
                    $channel = $discord->getChannel($channelId);

                    $channel->getMessageHistory(["limit" => 100])->done(
                        function ($messages) use ($channel, $messageCount) {
                            foreach ($messages as $message) {
                                $message->delete()->done(
                                    function () use ($message) {
                                        echo "Mensagem deletada: $message->content",
                                            PHP_EOL;
                                    },
                                    function ($error) {
                                        echo "Erro ao deletar mensagem: $error->getMessage()",
                                            PHP_EOL;
                                    }
                                );

                                $messageCount += 1;
                            }

                            $channel->sendMessage(
                                $messageCount == 1
                                    ? "Deletada $messageCount mensagem"
                                    : "Deletadas $messageCount mensagens"
                            );
                        },
                        function ($error) {
                            echo "Erro ao obter historico de mensagens: $error->getMessage()",
                                PHP_EOL;
                        }
                    );
                }
            }

            if ($command === "ping") {
                $message->reply("Pong!");
            }
        }
    });
});

$discord->run();
