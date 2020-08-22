<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\ProxyController;
use Illuminate\Console\Command;

class MaintainProxies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxies:maintain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duy trì số lượng proxy đủ để khách vào tạo bot là có sẵn';

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
        $proxyController = new ProxyController();
        return $proxyController->maintainProxies();
    }
}
