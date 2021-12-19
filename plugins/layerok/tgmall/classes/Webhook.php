<?php namespace Layerok\TgMall\Classes;

use League\Event\Emitter;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Events\UpdateWasReceived;
use Log;


class Webhook
{
    public function __construct()
    {
        if (env('TERMINATE_TELEGRAM_COMMANDS')) {
            return;
        };
        $emitter = new Emitter();

        $emitter->addListener(UpdateWasReceived::class, function ($event) {
            $update = $event->getUpdate();
            $telegram = $event->getTelegram();


            if ($update->detectType() === 'callback_query') {
                $rawResponse = $update->getRawResponse();

                $callbackQueryId = $rawResponse['callback_query']['id'];

                $rawResponse['message'] = $rawResponse['callback_query']['message'];
                $rawResponse['message']['text'] = $rawResponse['callback_query']['data'];
                unset($rawResponse['callback_query']);

                $hackedUpdate = new \Telegram\Bot\Objects\Update($rawResponse);


                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId
                ]);

                $command = ltrim(explode(' ', $rawResponse['message']['text'])[0], '/');

                if ($command === Constants::NOPE) {
                    return;
                }

                Telegram::getCommandBus()->execute(
                   $command, $hackedUpdate, []
                );
            }
        });

        Telegram::setEventEmitter($emitter);

        Telegram::commandsHandler(true);

        Log::debug('[---------END-----------');
    }
}
