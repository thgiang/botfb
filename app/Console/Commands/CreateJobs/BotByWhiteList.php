<?php

namespace App\Console\Commands\CreateJobs;

use App\Jobs\BotFacebook;
use App\Jobs\CrawlNewGroupPost;
use App\Jobs\CrawlNewPost;
use App\Models\Bot;
use App\Models\WhiteGroupIds;
use App\Models\WhiteListIds;
use Illuminate\Console\Command;

class BotByWhiteList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-jobs:bot-by-white-list';

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
        $fbIds = array();
        $fbGroupIds = array();
        WhiteListIds::chunkById(100, function ($whiteLists) use ($fbIds) {
            foreach ($whiteLists as $whiteList) {
                if (!in_array($whiteList->fb_id, $fbIds)) {
                    $bot = Bot::where('id', $whiteList->bot_id)->first();
                    if ($bot && $bot->count_error < config('bot.max_try_time')) {
                        $fbIds[] = $whiteList->fb_id;
                        CrawlNewPost::dispatch($whiteList->fb_id);
                    }
                }
            }
        });

        WhiteGroupIds::chunkById(100, function ($whiteGroups) use ($fbGroupIds) {
            foreach ($whiteGroups as $whiteGroup) {
                if (!in_array($whiteGroup->fb_id, $fbGroupIds)) {
                    $bot = Bot::where('id', $whiteGroup->bot_id)->first();
                    if ($bot && $bot->count_error < config('bot.max_try_time')) {
                        $fbGroupIds[] = $whiteGroup->fb_id;
                        CrawlNewGroupPost::dispatch($whiteGroup->fb_id);
                    }
                }
            }
        });
    }
}
