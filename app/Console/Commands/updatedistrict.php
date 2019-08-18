<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class updatedistrict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = ' v';

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
        DB::table('districts')
            ->where('district_type', 0)
            ->update(['district_type' => 5,]);
        $this->info('updated district');
    }
}
