<?php

namespace Layerok\TgMall\Classes\Callbacks;

use Layerok\TgMall\Classes\Constants;
use Layerok\TgMall\Classes\Messages\OrderCommentHandler;
use Layerok\TgMall\Classes\Messages\OrderDeliveryAddressHandler;
use Layerok\TgMall\Classes\Traits\Lang;
use OFFLINE\Mall\Models\ShippingMethod;
use Telegram\Bot\Keyboard\Keyboard;

class ChoseDeliveryMethodHandler extends CallbackQueryHandler
{
    protected $middlewares = [
        \Layerok\TgMall\Classes\Middleware\CheckBranchMiddleware::class,
        \Layerok\TgMall\Classes\Middleware\CheckCartMiddleware::class
    ];

    public function handle()
    {
        $id = $this->arguments['id'];
        $this->state->mergeOrderInfo([
            'delivery_method_id' => $id
        ]);

        if ($id == 3) {
            // доставка курьером
            \Telegram::sendMessage([
                'text' => 'Введите адрес доставки',
                'chat_id' => $this->update->getChat()->id,
            ]);
            $this->state->setMessageHandler(OrderDeliveryAddressHandler::class);

            return;
        }

        // самовывоз
        \Telegram::sendMessage([
            'text' => 'Комментарий к заказу',
            'chat_id' => $this->update->getChat()->id,
        ]);
        $this->state->setMessageHandler(OrderCommentHandler::class);

    }
}
