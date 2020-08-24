<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\TokenController;
use Illuminate\Console\Command;

class MaintainTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:maintain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quản lý số lượng/chất lượng token trong hệ thống';

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
        $tokenController = new TokenController();
        return $tokenController->maintainSystemTokens();
    }
}
