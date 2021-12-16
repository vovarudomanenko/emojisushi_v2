<?php namespace Layerok\TgMall\Commands;

use OFFLINE\Mall\Models\Product;
use Telegram\Bot\Commands\Command;
use Layerok\TgMall\Traits\Lang;
use Layerok\TgMall\Traits\Warn;
use Telegram\Bot\Keyboard\Keyboard;


class UpdateQuantityCommand extends Command
{
    use Lang;
    use Warn;

    protected $name = "update_qty";

    protected $pattern = "{product_id} {quantity}";

    public $product;
    public $limit = 10;
    /**
     * @var string Command Description
     */
    protected $description = "Update product quantity";

    public function validate() {
        if ($this->arguments['quantity'] > 10 || $this->arguments['quantity'] < 1) {
            $this->warn("Quantity of product can not be more than {$this->limit}");
            return false;
        }

        if(!isset($this->arguments['product_id'])) {
            $this->warn("To update product quantity you need to provide product_id");
            return false;
        }

        $this->product = Product::find($this->arguments['product_id']);
        if(!$this->product) {
            $this->warn("Trying to update quantity of product that does not exist with id {$this->arguments['product_id']}");
            return false;
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function handle()
    {
        if(env('TERMINATE_TELEGRAM_COMMANDS')) {
            return;
        };
        if(!$this->validate()) {
            return;
        }

        $quantity = $this->arguments['quantity'];
        //$positionId = $callback->position_id;

        $update = $this->getUpdate();
        $from = $update->getMessage()->getFrom();
        $chat = $update->getChat();
        $message = $update->getMessage();

        //todo: здесь должна быть проверка, если товар в корзине, то ничего не делаем
        $k = new Keyboard();
        $k->inline();
        $btn1 = $k::inlineButton([
            'text' => $this->lang('minus'),
            'callback_data' => implode(
                ' ',
                ['/update_qty', $this->product['id'], ($quantity - 1)]
            )
        ]);
        $btn2 = $k::inlineButton([
            'text' => $quantity . '/10',
            'callback_data' => 'placeholder'
        ]);
        $btn3 = $k::inlineButton([
            'text' => $this->lang('plus'),
            'callback_data' => implode(
                ' ',
                ['/update_qty', $this->product['id'], ($quantity + 1)]
            )
        ]);
        $k->row($btn1, $btn2, $btn3);

        $btn4 = $k::inlineButton([
            'text' => str_replace("*price*", $this->product->price()->toArray()['price_formatted'], $this->lang("in_basket_button_title")),
            'callback_data' => '/addtobasket'
        ]);

        $k->row($btn4);

        // Очень интересный момент, еслу у какой-то кнопки callback_data будет отсутствовать
        // или будет пустой строкой, такая клавиатура не обновится
        \Telegram::editMessageReplyMarkup([
            'chat_id' => $chat->id,
            'message_id' => $message->message_id,
            'reply_markup' => $k->toJson()
        ]);
    }
}
