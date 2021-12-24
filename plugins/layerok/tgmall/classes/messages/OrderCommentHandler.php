<?php

namespace Layerok\TgMall\Classes\Messages;

use Layerok\TgMall\Classes\Markups\ConfirmOrderReplyMarkup;
use Layerok\TgMall\Classes\Utils\CheckoutUtils;
use Lovata\BaseCode\Classes\Helper\ReceiptUtils;
use OFFLINE\Mall\Models\Cart;
use Telegram\Bot\Keyboard\Keyboard;

class OrderCommentHandler extends AbstractMessageHandler
{
    public function handle()
    {
        $this->state->mergeOrderInfo([
            'comment' => $this->text
        ]);

        $cart = Cart::byUser($this->customer->user);

        $data = CheckoutUtils::prepareData($this->state, $this->customer, $cart);
        $message = ReceiptUtils::makeReceipt('Подтверждаете заказ?', $data);


        \Telegram::sendMessage([
            'chat_id' => $this->chat->id,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => ConfirmOrderReplyMarkup::getKeyboard()
        ]);
        $this->state->setMessageHandler(null);
    }
}
