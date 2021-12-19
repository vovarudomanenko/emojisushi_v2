<?php namespace Layerok\TgMall\Commands;

use Layerok\TgMall\Traits\Warn;
use Lovata\BaseCode\Models\Branches;
use OFFLINE\Mall\Models\Customer;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class ChoseBranchCommand extends Command
{
    use Warn;
    protected $name = "chosebranch";
    protected $description = "Use this command to chose restaurant location";
    protected $pattern = "{id}";

    protected function validate(): bool
    {
        return true;
    }

    public function handle()
    {
        if (!$this->validate()) {
            return;
        }

        $this->chose();
    }

    public function chose()
    {
        $update = $this->getUpdate();
        $chat = $update->getChat();

        $branch = Branches::where('id', '=', $this->arguments['id'])->first();

        if (!isset($branch)) {
            $this->warn("You provided non existent restaurant id");
            return;
        }

        $customer = Customer::where('tg_chat_id', '=', $chat->id)->first();

        if (!isset($customer)) {
            // customer must be created on this stage
            \Log::error('somehow user was not created');
            return;
        }

        $customer->branch_id = $branch->id;
        $customer->save();

        $response = $this->replyWithMessage([
            'chat_id' => $chat->id,
            'text' => 'Вы выбрали заведение: ' . $branch->name
        ]);

        \Telegram::pinChatMessage([
            'chat_id' => $chat->id,
            'message_id' => $response['message_id']
        ]);

        $this->triggerCommand('start');
    }
}