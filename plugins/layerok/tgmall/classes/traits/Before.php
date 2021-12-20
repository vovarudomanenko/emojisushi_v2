<?php namespace Layerok\TgMall\Classes\Traits;

use Layerok\TgMall\Models\State;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\User;
use Telegram\Bot\Answers\Answerable;

trait Before {
    use Answerable;
    /**
     * @var Customer
     */
    public $customer;
    /**
     * @var State
     */
    public $state;

    /**
     * @var Cart
     */
    public $cart;


    public function isSpotChosen(): bool
    {
        if (!isset($this->customer->branch)) {
            return false;
        }
        return true;
    }

    public function before($checkBranch = true)
    {
        $update = $this->getUpdate();
        $chat = $update->getChat();
        $from = $update->getMessage()->getFrom();

        $this->customer = Customer::where('tg_chat_id', '=', $chat->id)->first();

        if (!$this->customer) {
            $pass = "qweasdqweasd";
            $user = User::create([
                'name' => "jonh",
                'password' => $pass,
                'password_confirmation' => $pass
            ]);
            $this->customer = Customer::create([
                "tg_chat_id" => $chat->id,
                "firstname" => $from->firstName,
                "lastname"  => $from->lastName,
                "tg_username" => $from->username,
                "user_id" => $user->id
            ]);
        }

        $this->cart = Cart::byUser($this->customer->user);

        $this->state = State::where('chat_id', '=', $chat->id)->first();
        //State::truncate();
        //\Log::info(State::all());

       /* if (!isset($this->state)) {
            $this->state = State::create([
                [
                    'chat_id' => $chat->id,
                    'state' => [
                        'command' => $this->getName()
                    ]
                ]
            ])->first();
        } else {
            $upd = ['command' => $this->getName()];
            if ($this->getName() !== 'checkout') {
                $upd['step'] = null;
            }
            $this->state->update([
                'state' => array_merge(
                    $this->state->state,
                    $upd
                )
            ]);
        }*/

        if ($checkBranch && !$this->isSpotChosen()) {
            $this->triggerCommand('listbranch');
            exit;
        }
    }
}
