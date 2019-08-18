<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Helpers\GoogleMapsHelper;
use App\Http\Requests\PriceRequest;
use App\Models\Booking;
use App\Models\District;
use App\Models\InterMunicipalUP;
use App\Models\Province;
use App\Models\SpecialPrice;
use App\Models\ProvincialUP;
use App\Models\SpecialUP;
use App\Models\Ward;
use function dd;
use Illuminate\Http\Request;
use App\Http\Requests\SearchPriceRequest;
use App\Http\Controllers\Controller;
use function json_decode;
use function number_format;
use function redirect;
use function url;

class PriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['getLocation','searchPrice', 'postSearchPrice']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $breadcrumb = ['Tra cứu giá cước'];

    protected function getLocation($province, $district, $ward, $home_number)
    {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $home_number);
        return $mapResults;
    }

    public function searchPrice()
    {
        return view('admin.elements.price.search', ['active' => 'search_price', 'breadcrumb' => $this->breadcrumb]);

    }

    public function postSearchPrice(SearchPriceRequest $req)
    {
        $lat_fr = 0;
        $lng_fr = 0;
        $lat_to = 0;
        $lng_to = 0;
        $mapResults_fr = $this->getLocation($req->province_id_fr, $req->district_id_fr, $req->ward_id_fr, $req->home_number_fr);
        if (isset($mapResults_fr->geometry)) {
            if (isset($mapResults_fr->geometry->location)) {
                $lat_fr = $mapResults_fr->geometry->location->lat;
                $lng_fr = $mapResults_fr->geometry->location->lng;
            }
        }
        $mapResults_to = $this->getLocation($req->province_id_to, $req->district_id_to, $req->ward_id_to, $req->home_number_to);
        if (isset($mapResults_to->geometry)) {
            if (isset($mapResults_to->geometry->location)) {
                $lat_to = $mapResults_to->geometry->location->lat;
                $lng_to = $mapResults_to->geometry->location->lng;
            }
        }
        $data = (object) [
            "weight" => $req->weight,
            "cod" => $req->cod,
            "receive_type" => $req->receive_type,
            "transport_type" => $req->transport_type,
            "sender" => [
                "address" => [
                    "district" => $req->district_id_fr,
                    "homenumber" => $req->homenumber_fr,
                    "province" => $req->province_id_fr,
                    "ward" => $req->ward_id_fr,
                ],
                "location" => [

                    "lat" => $lat_fr,
                    "lng" => $lng_fr
                ]
            ],
            "receiver" => [
                "address" => [
                    "district" => $req->district_id_to,
                    "homenumber" => $req->homenumber_to,
                    "province" => $req->province_id_to,
                    "ward" => $req->ward_id_to,
                ],
                "location" => [
                    "lat" => $lat_to,
                    "lng" => $lng_to
                ]
            ]
        ];
        $result = number_format(Booking::Pricing($data));
        return redirect()->back()->with('data', $result . ' VND')->withInput();
    }

    public function index()
    {
        $this->middleware('admin');
        $this->breadcrumb = ['Giá cước'];
        return view('admin.elements.price.index', ['active' => 'price', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($type, $id)
    {
        $this->breadcrumb = ['Giá cước'];
        switch ($type){
            // case 'InterMunicipalUP' :
            //     $this->breadcrumb[] = 'Đơn giá liên tỉnh';
            //     $price = InterMunicipalUP::find($id)->price;
            //     $weight = InterMunicipalUP::find($id)->weight;
            //     break;

            // case 'SpecialUP' :
            //     $this->breadcrumb[] = 'Đơn giá liên tỉnh đặc biệt';
            //     $price = SpecialUP::find($id)->price;
            //     $weight = SpecialUP::find($id)->weight;
            //     break;

            // case 'SpecialPrice' :
            //     $this->breadcrumb[] = 'Đơn giá > 2000gram';
            //     $price = SpecialPrice::find($id)->price;
            //     $weight = SpecialPrice::find($id)->weight;
            //     break;

            case 'ProvincialUPVip' :
                $this->breadcrumb[] = 'Đơn giá nội thành VIP';
                break;

            case 'ProvincialUPPro' :
                $this->breadcrumb[] = 'Đơn giá nội thành Pro';
                break;
            default :
                $this->breadcrumb[] = 'Đơn giá nội thành';


        }
        $price = ProvincialUP::find($id)->price;
        $weight = ProvincialUP::find($id)->weight;
        $price_plus = ProvincialUP::find($id)->price_plus;
        $weight_plus = ProvincialUP::find($id)->weight_plus;
        return view('admin.elements.price.add', ['type' => $type, 'id' => $id, 'price' => $price, 'weight' => $weight, 'active' => 'price', 'price_plus' => $price_plus, 'weight_plus' => $weight_plus, 'active' => 'price', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PriceRequest $request, $type, $id)
    {
        // switch ($type){
        //     case 'InterMunicipalUP' :
        //         $price = InterMunicipalUP::find($id);
        //         break;

        //     case 'SpecialUP' :
        //         $price = SpecialUP::find($id);
        //         break;

        //     case 'SpecialPrice' :
        //         $price = SpecialPrice::find($id);
        //         break;

        //     case 'ProvincialUPVip' :
        //         $price = ProvincialUP::find($id);
        //         break;

        //     case 'ProvincialUPPro' :
        //         $price = ProvincialUP::find($id);
        //         break;
        //     default :
        //         $price = ProvincialUP::find($id);
        // }
        $price = ProvincialUP::find($id);
        $price->price = $request->price;
        $price->weight = $request->weight;
        $price->price_plus = $request->price_plus;
        $price->weight_plus = $request->weight_plus;
        $price->save();
        return redirect(url('admin/price'))->with(['success'=> 'Cập nhật đơn giá thành công', 'selected' => $type]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
