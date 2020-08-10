<?php

namespace App\Console\Commands\CreateJobs;

use App\Jobs\BotFacebook;
use App\Models\Bot;
use Illuminate\Console\Command;

class BotByTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-jobs:bot-by-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Bot::where('count_error', '<', config('bot.max_try_time'))->where(function ($query) {
            $query->where('next_reaction_time', '<=', time())->orWhere('next_comment_time', '<=', time());
        })->chunkById(100, function ($bots) {
            foreach ($bots as $bot) {
                // Nếu là whitelist thì đã có job BotByWhiteList xử lý nên ko xử lý ở đây nữa.
                if ($bot->bot_target != 'whitelist') {
                    BotFacebook::dispatch($bot->id);
                }
            }
        });
    }
}
