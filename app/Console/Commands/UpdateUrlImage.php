<?php

namespace App\Console\Commands;

use App\Models\ReportImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function print_r;

class UpdateUrlImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_url';

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
        $report = ReportImage::all();
        DB::beginTransaction();
        try {
            foreach ($report as $rp) {
                if (strpos($rp->image, 'http://www.smartexpress.vn/') !== false) {
                    $data = str_replace("http://www.smartexpress.vn/", "", $rp->image);
                    ReportImage::where('id', $rp->id)->update(['image' => $data]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $this->info('updated url success');
    }
}
