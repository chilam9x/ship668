@extends('admin.app')

<style>
    table {
        width: 100% !important;
    }
</style>
@section('title')
    Giá cước
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row" style="margin-bottom: 20px">
        <div class="col-lg-3">
            <label><b style="color: rgba(85,5,5,0.98)">CHỌN LOẠI ĐƠN GIÁ</b></label>
            <select onchange="loadTable()" id="unit_price" name="unit_price"
                    class="form-control">
                <option value="ProvincialUP" {{ \Session::get('selected') == 'ProvincialUP' ? 'selected' : '' }}>Đơn giá
                    nội thành
                </option>
                <option value="ProvincialUPVip" {{ \Session::get('selected') == 'ProvincialUPVip' ? 'selected' : '' }}>Đơn giá
                    nội thành (cho khách VIP)
                </option>
                <option value="ProvincialUPPro" {{ \Session::get('selected') == 'ProvincialUPPro' ? 'selected' : '' }}>Đơn giá
                    nội thành (cho khách Pro)
                </option>
                <!-- <option value="InterMunicipalUP" {{ \Session::get('selected') == 'InterMunicipalUP' ? 'selected' : '' }}>
                    Đơn giá liên tỉnh
                </option>
                <option value="SpecialUP" {{ \Session::get('selected') == 'SpecialUP' ? 'selected' : '' }}>Đơn giá liên
                    tỉnh đặc biệt
                </option>
                <option value="SpecialPrice" {{ \Session::get('selected') == 'SpecialPrice' ? 'selected' : '' }}>Đơn giá
                    > 2000 (gram)
                </option> -->
            </select>
        </div>
        <div class="col-lg-6" style="text-align: right; margin-top: 25px">
            <a class="btn btn-primary" data-toggle="modal" data-target="#importData">Import</a>
            <a id="export" href="" class="btn btn-warning">Export</a>
        </div>
    </div>
    <div class="modal fade" id="importData" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: red" class="modal-title">Giao diện import dữ liệu</h4>
                </div>
                <form id="import" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <input type="hidden" id="district_id" name="district">
                            <div class="col-lg-12">
                                <div class="{{--has-error--}} form-group">
                                    <label style="margin-bottom: 10px" class="control-label">Tải lên file dữ liệu</label>
                                    <input type="file" name="import" id="exampleInputFile">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Thực hiện</button>
                        <button onclick="$('#importData').modal('hide')" type="button"
                                class="btn btn-default" data-dismiss="modal">Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if ($errors->has('import'))
        @foreach ($errors->get('import') as $error)
            <script> alert('Import dữ liệu không thành công {!! $error !!}')</script>
        @endforeach
    @endif
    <div class="row" id="ProvincialUP">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'provincial',
               'title' => [
                       'caption' => 'Dữ liệu đơn giá nội thành',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/provincial"),
               'columns' => [
                       ['data' => 'type_name', 'title' => 'Loại thành phố'],
                       ['data' => 'weight', 'title' => 'Khối lượng'],
                       ['data' => 'price', 'title' => 'Giá (VND)'],
                       ['data' => 'weight_plus', 'title' => 'Khối lượng tăng thêm'],
                       ['data' => 'price_plus', 'title' => 'Giá tăng thêm (VND)'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="row" id="ProvincialUPVip">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'provincialVip',
               'title' => [
                       'caption' => 'Dữ liệu đơn giá nội thành (cho khách VIP)',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/provincial-vip"),
               'columns' => [
                       ['data' => 'type_name', 'title' => 'Loại thành phố'],
                       ['data' => 'weight', 'title' => 'Khối lượng'],
                       ['data' => 'price', 'title' => 'Giá (VND)'],
                       ['data' => 'weight_plus', 'title' => 'Khối lượng tăng thêm'],
                       ['data' => 'price_plus', 'title' => 'Giá tăng thêm (VND)'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="row" id="ProvincialUPPro">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'provincialPro',
               'title' => [
                       'caption' => 'Dữ liệu đơn giá nội thành (cho khách Pro)',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/provincial-pro"),
               'columns' => [
                       ['data' => 'type_name', 'title' => 'Loại thành phố'],
                       ['data' => 'weight', 'title' => 'Khối lượng'],
                       ['data' => 'price', 'title' => 'Giá (VND)'],
                       ['data' => 'weight_plus', 'title' => 'Khối lượng tăng thêm'],
                       ['data' => 'price_plus', 'title' => 'Giá tăng thêm (VND)'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="row" id="InterMunicipalUP">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
                   'id' => 'inter_municipal',
                   'title' => [
                           'caption' => 'Dữ liệu đơn giá liên tỉnh',
                           'icon' => 'fa fa-table',
                           'class' => 'portlet box red',
                   ],
                   'url' => url('ajax/interMunicipal'),
                   'columns' => [
                           ['data' => 'type_name', 'title' => 'Loại thành phố'],
                           ['data' => 'km', 'title' => 'Khoảng cách'],
                           ['data' => 'weight', 'title' => 'Khối lượng'],
                           ['data' => 'price', 'title' => 'Giá (VND)'],
                           ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                       ]
                   ])
        </div>
    </div>
    <div class="row" id="SpecialUP">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'special_inter_municipal',
               'title' => [
                       'caption' => 'Dữ liệu đơn giá liên tỉnh đặc biệt',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url('ajax/special_inter_municipal'),
               'columns' => [
                       ['data' => 'from_name', 'title' => 'Tỉnh xuất phát'],
                       ['data' => 'to_name', 'title' => 'Tỉnh kết thúc'],
                       ['data' => 'weight', 'title' => 'Khối lượng'],
                       ['data' => 'price', 'title' => 'Giá (VND)'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="row" id="SpecialPrice">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'special_price',
               'title' => [
                       'caption' => 'Dữ liệu đơn giá > 2000 (gram)',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box red',
               ],
               'url' => url('ajax/special_price'),
               'columns' => [
                       ['data' => 'type_name', 'title' => 'Loại thành phố'],
                       ['data' => 'from_name', 'title' => 'Tỉnh xuất phát'],
                       ['data' => 'to_name', 'title' => 'Tỉnh kết thúc'],
                       ['data' => 'km', 'title' => 'Khoảng cách'],
                       ['data' => 'price', 'title' => 'Giá (VND)'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
@push('script')
    <script>
        loadTable();

        function loadTable() {
            var unit_price = $('#unit_price').val();
            if (unit_price === 'ProvincialUP') {
                $("#import").attr("action", "{{ url('admin/import/provincial') }}");
                $("#export").attr("href", "{{ url('admin/export/provincial') }}");
                $('#ProvincialUP').show(1000);
                $('#ProvincialUPVip').hide();
                $('#ProvincialUPPro').hide();
                $('#InterMunicipalUP').hide();
                $('#SpecialUP').hide();
                $('#SpecialPrice').hide();
            }
            if (unit_price === 'ProvincialUPVip') {
                $("#import").attr("action", "{{ url('admin/import/provincialVip') }}");
                $("#export").attr("href", "{{ url('admin/export/provincialVip') }}");
                $('#ProvincialUP').hide();
                $('#ProvincialUPVip').show(1000);
                $('#ProvincialUPPro').hide();
                $('#InterMunicipalUP').hide();
                $('#SpecialUP').hide();
                $('#SpecialPrice').hide();
            }
            if (unit_price === 'ProvincialUPPro') {
                $("#import").attr("action", "{{ url('admin/import/provincialPro') }}");
                $("#export").attr("href", "{{ url('admin/export/provincialPro') }}");
                $('#ProvincialUP').hide();
                $('#ProvincialUPVip').hide();
                $('#ProvincialUPPro').show(1000);
                $('#InterMunicipalUP').hide();
                $('#SpecialUP').hide();
                $('#SpecialPrice').hide();
            }
            if (unit_price === 'InterMunicipalUP') {
                $("#import").attr("action", "{{ url('admin/import/interMunicipal') }}");
                $("#export").attr("href", "{{ url('admin/export/interMunicipal') }}");
                $('#ProvincialUP').hide();
                $('#ProvincialUPVip').hide();
                $('#ProvincialUPPro').hide();
                $('#InterMunicipalUP').show(1000);
                $('#SpecialUP').hide();
                $('#SpecialPrice').hide();
            }
            if (unit_price === 'SpecialUP') {
                $("#import").attr("action", "{{ url('admin/import/special') }}");
                $("#export").attr("href", "{{ url('admin/export/special') }}");
                $('#ProvincialUP').hide();
                $('#ProvincialUPVip').hide();
                $('#ProvincialUPPro').hide();
                $('#InterMunicipalUP').hide();
                $('#SpecialUP').show(1000);
                $('#SpecialPrice').hide();
            }
            if (unit_price === 'SpecialPrice') {
                $("#import").attr("action", "{{ url('admin/import/special_price') }}");
                $("#export").attr("href", "{{ url('admin/export/special_price') }}");
                $('#ProvincialUP').hide();
                $('#ProvincialUPVip').hide();
                $('#ProvincialUPPro').hide();
                $('#InterMunicipalUP').hide();
                $('#SpecialUP').hide();
                $('#SpecialPrice').show(1000);
            }
        }
    </script>
@endpush

