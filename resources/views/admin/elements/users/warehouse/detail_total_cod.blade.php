@extends('admin.app')

@section('title')
    Shipper
@endsection

@section('sub-title')
    chi tiết tổng {{ $type == 'cod' ? 'COD thu hộ' : 'phí giao hàng' }} trên từng đơn hàng
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="portlet light bordered">
                @if (!empty($bookUsers) && count($bookUsers) > 0)
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Shipper {{ $name }}</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    <table id="" class="display table table-bordered dataTable no-footer" role="grid" aria-describedby="agency_info">
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Tên đơn hàng</th>
                            <th>Ngày hoàn thành</th>
                            @if($type == 'ship')
                            <th>Phí vận chuyển (VND)</th>
                            <th>Phí phát sinh</th>
                            @else
                            <th>COD thu hộ (VND)</th>
                            @endif
                        </tr>

                        <?php $totalCOD = 0; $totalPrice = 0 ?>
                        @foreach ($bookUsers as $item)
                        <tr>
                            <td>{{ $item->uuid }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ date('d/m/Y', strtotime($item->completed_at))}}</td>
                            @if($type == 'ship')
                            <?php $totalPrice += ($item->price + $item->incurred) ?>
                            <td><span class="text-success">{{ number_format($item->price) }}</span></td>
                            <td><span class="text-success">{{ number_format($item->incurred) }}</span></td>
                            @else
                            <td><span class="text-success">{{ number_format($item->COD) }}</span></td>
                            <?php $totalCOD += $item->COD ?>
                            @endif
                        </tr>  
                        @endforeach
                    </table>
                    <h4 align="right">
                        <b>Tổng {{ $type == 'cod' ? 'COD thu hộ' : 'phí giao hàng' }}: </b>
                        <b><span class="text-success">{{ $type == 'cod' ? number_format($totalCOD) : number_format($totalPrice)  }}</span></b>
                    </h4>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-12 mt-4" align="right" style="margin-top: 10px">
                        <a href="{{ url('admin/shippers') }}" class="btn btn-primary">Quay lại</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
    </script>
@endpush
