<?php namespace Layerok\TgMall\Classes\Middleware;

use Layerok\TgMall\Classes\Buttons\ChoseBranchButton;
use Lovata\BaseCode\Models\Branches;
use OFFLINE\Mall\Models\Customer;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class CheckNotChosenBranchMiddleware extends AbstractMiddleware
{

    public function isSpotChosen(): bool
    {
        $id = $this->update->getChat()->id;
        $customer = Customer::where('tg_chat_id', '=', $id)->first();

        if (!isset($customer)) {
            return false;
        }

        if (!isset($customer->branch)) {
            return false;
        }
        return true;
    }

    public function run(): bool
    {
        if (!$this->isSpotChosen()) {
            return false;
        }
        return true;
    }

    public function onFailed():void
    {
        $branches = Branches::all();
        $k = new Keyboard();
        $k->inline();
        $branches->map(function ($branch) use ($k) {
            $btn = new ChoseBranchButton($branch);
            $k->row($k::inlineButton($btn->getData()));
        });

        $this->telegram->sendMessage([
            'chat_id' =>  $this->update->getChat()->id,
            'text' => 'Выберите заведение',
            'reply_markup' => $k
        ]);
    }


}
