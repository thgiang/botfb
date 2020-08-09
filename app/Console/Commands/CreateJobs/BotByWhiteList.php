<?php

namespace App\Console\Commands\CreateJobs;

use App\Jobs\BotFacebook;
use App\Jobs\CrawlNewPost;
use App\Models\Bot;
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
        WhiteListIds::chunkById(100, function ($whiteLists) use ($fbIds) {
            foreach ($whiteLists as $whiteList) {
                if (!in_array($whiteList->fb_id, $fbIds)) {
                    $fbIds[] = $whiteList->fb_id;
                    CrawlNewPost::dispatch($whiteList->fb_id);
                }
            }
        });
    }
}
