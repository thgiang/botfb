<?php

namespace App\Console\Commands\CreateJobs;

use App\Jobs\BotFacebookV2;
use App\Models\Bot;
use Illuminate\Console\Command;

class WakeupBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-jobs:wakeup-bot';

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
            $query->where('next_reaction_time', '<=', time())
                ->orWhere('next_comment_time', '<=', time())
                ->orWhere('white_group_run_mode', 'asap')
                ->orWhere('white_list_run_mode', 'asap');
        })->chunkById(100, function ($bots) {
            foreach ($bots as $bot) {
                BotFacebookV2::dispatch($bot->id);
            }
        });
    }
}
