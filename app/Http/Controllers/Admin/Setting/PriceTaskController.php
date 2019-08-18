<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Models\DistrictType;
use App\Models\InterMunicipalUP;
use App\Models\Province;
use App\Models\ProvincialUP;
use App\Models\SpecialPrice;
use App\Models\SpecialUP;
use function count;
use Excel;
use function dd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function url;

class PriceTaskController extends Controller
{
    public function importProvincial(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            ProvincialUP::where('type', 0)->delete();
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $district_type = DistrictType::where('name', $row[0])->first()->id;
                    $data = new ProvincialUP;
                    $data->district_type = $district_type;
                    $data->weight = $row[1];
                    $data->price = $row[2];
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá nội thành thành công', 'selected' => 'ProvincialUP']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'ProvincialUP']);
        }
    }

    public function importProvincialVip(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            ProvincialUP::where('type', 1)->delete();
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $district_type = DistrictType::where('name', $row[0])->first()->id;
                    $data = new ProvincialUP;
                    $data->district_type = $district_type;
                    $data->weight = $row[1];
                    $data->price = $row[2];
                    $data->type = 1;
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá nội thành thành công', 'selected' => 'ProvincialUPVip']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'ProvincialUPVip']);
        }
    }

    public function importProvincialPro(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            ProvincialUP::where('type', 2)->delete();
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $district_type = DistrictType::where('name', $row[0])->first()->id;
                    $data = new ProvincialUP;
                    $data->district_type = $district_type;
                    $data->weight = $row[1];
                    $data->price = $row[2];
                    $data->type = 2;
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá nội thành thành công', 'selected' => 'ProvincialUPPro']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'ProvincialUPPro']);
        }
    }

    public function importInterMunicipal(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $district_type = DistrictType::where('name', $row[0])->first()->id;
                    $data = InterMunicipalUP::find($id);
                    $data->district_type = $district_type;
                    $data->km = $row[1];
                    $data->weight = $row[2];
                    $data->price = $row[3];
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá liên tỉnh thành công', 'selected' => 'InterMunicipalUP']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'InterMunicipalUP']);
        }
    }

    public function importSpecial(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $from = Province::where('name', $row[0])->first()->id;
                    $to = Province::where('name', $row[1])->first()->id;
                    $data = SpecialUP::find($id);
                    $data->province_from = $from;
                    $data->province_to = $to;
                    $data->weight = $row[2];
                    $data->price = $row[3];
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá liên tỉnh đặc biệt thành công', 'selected' => 'SpecialUP']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'SpecialUP']);
        }
    }

    public function importSpecialPrice(ImportRequest $req)
    {
        DB::beginTransaction();
        try {
            Excel::load($req->import, function ($reader) {
                $reader->noHeading();
                $reader->skipRows(5);
                $reader->skipColumns(2);
                $reader = $reader->toArray();
                $id = 1;
                foreach ($reader as $row) {
                    $district_type = $row[0] != null ? DistrictType::where('name', $row[0])->first()->id : null;
                    $from = $row[1] != null ? Province::where('name', $row[1])->first()->id : null;
                    $to = $row[2] != null ? Province::where('name', $row[1])->first()->id : null;
                    $data = SpecialPrice::find($id);
                    $data->district_type = $district_type;
                    $data->province_from = $from;
                    $data->province_to = $to;
                    $data->km = $row[3];
                    $data->price = $row[4];
                    $data->save();
                    $id++;
                }
            });
            DB::commit();
            return redirect(url('admin/price'))->with(['success' => 'Import dữ liệu đơn giá liên tỉnh đặc biệt thành công', 'selected' => 'SpecialPrice' ]);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect(url('admin/price'))->with(['delete' => 'Import không thành công', 'selected' => 'SpecialPrice']);
        }
    }

    public function exportProvincial()
    {
        $price = DB::table('provincial_u_ps')->join('district_types', 'provincial_u_ps.district_type', '=', 'district_types.id')
            ->where('provincial_u_ps.type', 0)
            ->select('provincial_u_ps.price', 'provincial_u_ps.weight', 'district_types.name')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['district_type'] = $b->name;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/provincial.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->skipRows(3);

            $reader->sheet('list_booking', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng nội tỉnh')->export('xlsx');
    }

    public function exportProvincialVip()
    {
        $price = DB::table('provincial_u_ps')->join('district_types', 'provincial_u_ps.district_type', '=', 'district_types.id')
            ->where('provincial_u_ps.type', 1)
            ->select('provincial_u_ps.price', 'provincial_u_ps.weight', 'district_types.name')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['district_type'] = $b->name;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/provincialVip.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->skipRows(3);

            $reader->sheet('list_booking', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng nội tỉnh VIP')->export('xlsx');
    }

    public function exportProvincialPro()
    {
        $price = DB::table('provincial_u_ps')->join('district_types', 'provincial_u_ps.district_type', '=', 'district_types.id')
            ->where('provincial_u_ps.type', 2)
            ->select('provincial_u_ps.price', 'provincial_u_ps.weight', 'district_types.name')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['district_type'] = $b->name;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/provincialVip.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->skipRows(3);

            $reader->sheet('list_booking', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng nội tỉnh Pro')->export('xlsx');
    }

    public function exportInterMunicipal()
    {
        $price = InterMunicipalUP::with('districtTypes')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['district_type'] = $b->type_name;
            $data['km'] = $b->km;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/interMunicipal.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->sheet('sheet1', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng liên tỉnh')->export('xlsx');
    }

    public function exportSpecial()
    {
        $price = SpecialUP::with('provinceFrom', 'provinceTo')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['from'] = $b->from_name;
            $data['to'] = $b->to_name;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/special.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->sheet('sheet1', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng liên tỉnh đặc biệt')->export('xlsx');
    }

    public function exportSpecialPrice()
    {
        $price = SpecialPrice::with('provinceFrom', 'provinceTo')->get();
        $result = [];
        $num = 1;
        foreach ($price as $b) {
            $data['Stt'] = $num;
            $data['type'] = $b->type_name;
            $data['from'] = $b->from_name;
            $data['to'] = $b->to_name;
            $data['km'] = $b->km;
            $data['price'] = $b->price;
            $num++;
            $result[] = $data;
        }
        $file_path = public_path('excel_temp/price/weightfrom2000.xlsx');
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->sheet('sheet1', function ($sheet) use ($result) {
                $sheet->fromArray($result, null, 'B6', true, false);
            });

        })->setFilename('Đơn hàng có khối lượng lớn hơn 2000gram')->export('xlsx');
    }
}
