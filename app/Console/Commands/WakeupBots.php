<?php

namespace App\Console\Commands;

use App\Jobs\BotFacebook;
use App\Models\Bot;
use Illuminate\Console\Command;

class WakeupBots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wakeup-bots';

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
        Bot::where('is_valid', 1)->where('next_run_time', '<=', time())->chunkById(100, function ($bots) {
            foreach ($bots as $bot) {
                BotFacebook::dispatch($bot->id);
           }
        });
    }
}