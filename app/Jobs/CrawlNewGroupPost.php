<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use App\Models\SystemToken;
use App\Models\WhiteListIds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrawlNewGroupPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fb_id;

    /**
     * Create a new job instance.
     *
     * @param string $fb_id
     */
    public function __construct($fb_id)
    {
        $this->fb_id = $fb_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

    }
}
