<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Liabilities;
use App\Models\Revenue;
use Carbon\Carbon;
use function dd;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:update';

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
        $time = \DB::table('revenues')->max('last_time');
        if ($time != null) {
            $booking = \DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
                ->where('bookings.status', 'completed')->where('bookings.sub_status', 'none')->where('bookings.completed_at', '>', $time)
                ->where('book_deliveries.category', 'send')->where('book_deliveries.status', 'completed')->where('book_deliveries.sending_active', 1)
                ->orWhere('bookings.status', 'return')->where('bookings.sub_status', 'none')->where('bookings.completed_at', '>', $time)
                ->where('book_deliveries.category', 'return')->where('book_deliveries.status', 'completed')->select('bookings.*')->get();
        } else {
            $booking = \DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
                ->where('bookings.status', 'completed')->where('bookings.sub_status', 'none')
                ->where('book_deliveries.category', 'send')->where('book_deliveries.status', 'completed')->where('book_deliveries.sending_active', 1)
                ->orWhere('bookings.status', 'return')->where('bookings.sub_status', 'none')
                ->where('book_deliveries.category', 'return')->where('book_deliveries.status', 'completed')->select('bookings.*')->get();
        }
        \DB::beginTransaction();
        try {
            foreach ($booking as $b) {
                $first_agency_id = $b->first_agency == null ? 1 : $b->first_agency;
                $last_agency_id = $b->last_agency == null ? 1 : $b->last_agency;
                $first_agency = Agency::where('id', $first_agency_id)->first();
                if ($first_agency != null) {
                    $first_discount = $first_agency->discount;
                } else {
                    $fa_check = Agency::where('id', 1)->first();
                    if ($fa_check != null) {
                        $first_discount = $fa_check->discount;
                    }
                }
                $last_agency = Agency::where('id', $last_agency_id)->first();
                if ($last_agency != null) {
                    $last_discount = $last_agency->discount;
                } else {
                    $la_check = Agency::where('id', 1)->first();
                    if ($la_check != null) {
                        $last_discount = $la_check->discount;
                    }
                }
                $total_price = $b->price + $b->incurred;
                if ($first_agency_id != $last_agency_id) {
                    if ($b->payment_type == 1) {
                        Revenue::insert([
                            [
                                'agency_id' => $first_agency_id,
                                'book_id' => $b->id,
                                'booking_revenue' => $total_price,
                                'agency_discount' => ($total_price * $first_discount) / 100,
                                'last_time' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            [
                                'agency_id' => $last_agency_id,
                                'book_id' => $b->id,
                                'booking_revenue' => 0,
                                'agency_discount' => ($total_price * ($last_discount / 2)) / 100,
                                'last_time' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]
                        ]);

                    } else {
                        Revenue::insert([
                            [
                                'agency_id' => $first_agency_id,
                                'book_id' => $b->id,
                                'booking_revenue' => 0,
                                'agency_discount' => ($total_price * ($first_discount / 2)) / 100,
                                'last_time' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ],
                            [
                                'agency_id' => $last_agency_id,
                                'book_id' => $b->id,
                                'booking_revenue' => $total_price,
                                'agency_discount' => ($total_price * $last_discount) / 100,
                                'last_time' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]
                        ]);
                    }
                }else{
                    Revenue::insert([
                        [
                            'agency_id' => $first_agency_id,
                            'book_id' => $b->id,
                            'booking_revenue' => $total_price,
                            'agency_discount' => ($total_price * $first_discount) / 100,
                            'last_time' => Carbon::now(),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    ]);
                }

            }
            \DB::commit();
            $this->info('update revenue successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->info('update revenue fail');
        }
    }
}
