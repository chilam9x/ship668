<?php

namespace App\Http\Controllers\Ajax;

use App\Models\InterMunicipalUP;
use App\Models\Liabilities;
use App\Models\PaidHistory;
use App\Models\ProvincialUP;
use App\Models\Revenue;
use App\Models\Setting;
use App\Models\SpecialPrice;
use App\Models\SpecialUP;
use App\Models\Version;
use function datatables;
use App\Http\Controllers\Controller;
use function dd;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function number_format;

class SettingController extends Controller
{
    public function getDiscount()
    {
        $data = Setting::all();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/discounts/' . $d->id . '/edit') . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/discounts/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function provincial()
    {
        // $data = ProvincialUP::where('type', 0)->groupBy('price')->get();
        $data = ProvincialUP::where('type', 0)->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/ProvincialUP/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('weight', function ($d) {
                return $d->weight > 0 ? '< ' . $d->weight . ' (gram)' : '';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->editColumn('weight_plus', function ($d) {
                return $d->weight > 0 ? $d->weight_plus . ' (gram)' : '';
            })
            ->editColumn('price_plus', function ($d) {
                return number_format($d->price_plus);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function provincialVip()
    {
        $data = ProvincialUP::where('type', 1)->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/ProvincialUPVip/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('weight', function ($d) {
                return $d->weight > 0 ? '< ' . $d->weight . ' (gram)' : '';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->editColumn('weight_plus', function ($d) {
                return $d->weight > 0 ? $d->weight_plus . ' (gram)' : '';
            })
            ->editColumn('price_plus', function ($d) {
                return number_format($d->price_plus);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function provincialPro()
    {
        $data = ProvincialUP::where('type', 2)->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/ProvincialUPPro/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('weight', function ($d) {
                return $d->weight > 0 ? '< ' . $d->weight . ' (gram)' : '';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->editColumn('weight_plus', function ($d) {
                return $d->weight > 0 ? $d->weight_plus . ' (gram)' : '';
            })
            ->editColumn('price_plus', function ($d) {
                return number_format($d->price_plus);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function interMunicipal()
    {
        $data = InterMunicipalUP::orderBy('price', 'asc')->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/InterMunicipalUP/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('weight', function ($d) {
                return '> ' . $d->weight . ' (gram)';
            })
            ->editColumn('km', function ($d) {
                return '> ' . $d->km . ' (km)';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function specialInterMunicipal()
    {
        $data = SpecialUP::orderBy('price', 'asc')->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/SpecialUP/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('weight', function ($d) {
                return '> ' . $d->weight . ' (gram)';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function specialPrice()
    {
        $data = SpecialPrice::orderBy('price', 'asc')->get();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/price/SpecialPrice/' . $d->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                /* $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/price/' . $d->id]]) .
                     '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                     Form::close() . '</div>';*/
                return implode(' ', $action);
            })
            ->editColumn('type_name', function ($d) {
                if ($d->district_type != 1) {
                    return $d->district_type != 0 ? $d->type_name : 'Liên tỉnh đặc biệt';
                }
                return 'Nội tỉnh';
            })
            ->editColumn('km', function ($d) {
                if ($d->district_type != 0 && $d->district_type != 1) {
                    return '> ' . $d->km . ' (km)';
                }
                return '';
            })
            ->editColumn('price', function ($d) {
                return number_format($d->price);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function paymentAgency(Request $request)
    {
        if ($request->paid != null) {
            if ($request->paid < 0) {
                return 'Số tiền thanh toán không được âm';
            } else {
                DB::beginTransaction();
                try {
                    $paid = new PaidHistory();
                    $paid->agency_id = $request->agency;
                    $paid->user_create = Auth::user()->id;
                    $paid->value = $request->paid;
                    if ($request->action_type == 3) {
                        $paid->type = 1;
                        $paid->status = 1;
                        $liabilities = Liabilities::where('agency_id', $request->agency)->first();
                        if ($liabilities == null) {
                            $liabilities = new Liabilities();
                            $liabilities->agency_id = $request->agency;
                        }
                        $liabilities->discount_paid += $request->paid;
                        $liabilities->save();
                    } else {
                        $paid->type = 0;
                        $paid->status = 0;
                    }
                    $paid->save();
                    DB::commit();
                    return ['success', $request->action_type];
                } catch (\Exception $e) {
                    DB::rollBack();
                    dd($e);
                }
            }
        } else {
            return false;
        }
    }

    public function turnoverPaid($id)
    {
        DB::beginTransaction();
        try {
            $paid = PaidHistory::find($id);
            if ($paid != null && $paid->status == 0) {
                $liabilities = Liabilities::where('agency_id', $paid->agency_id)->first();
                if ($liabilities == null) {
                    $liabilities = new Liabilities();
                }
                $liabilities->turnover_paid += $paid->value;
                $liabilities->save();
                $paid->status = 1;
                $paid->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return 'success';
    }

    public function version()
    {
        $data = Version::all();
        return datatables()->of($data)
            ->addColumn('action', function ($d) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/versions/' . $d->id . '/edit') . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                return implode(' ', $action);
            })->rawColumns(['action'])
            ->make(true);
    }
}
