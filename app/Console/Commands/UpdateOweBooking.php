<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOweBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:owe';

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
        DB::beginTransaction();
        try {
            Booking::with('deliveries')->where('status', 'sending')->where('paid', '>', 0)
                ->orWhere('status', 'completed')->where('paid', '>', 0)
                ->orWhere('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                    $query->where('category', 'return')->where('status', 'completed');
                })->update(['owe' => 1]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $this->info('updated booking owe success');
    }
}
