<div class="row">
    @if(isset($title))
        <div class="{!! $title['class'] !!}">
            <div class="portlet-title">
                <div class="caption">
                    @if(isset($title['icon']))
                        <i class="{!! $title['icon'] !!}" aria-hidden="true"> </i>
                    @endif
                    @if(isset($title['caption']))
                        {!! $title['caption'] !!}
                    @endif
                </div>
                <div class="tools"></div>
            </div>
            <div class="portlet-body">
                @if(isset($shipperOnline) && isset($shippers))
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-success">Shipper Online: {{ count($shipperOnline) }}</h4>
                        <h4 class="text-danger">Shipper Offline: {{ count($shippers) - count($shipperOnline) }}</h4>
                    </div>
                </div>
                @endif
                @if(isset($customSearch))
                <div class="row">
                    <div class="col-md-2">
                        Trạng thái phân công
                    </div>
                    <div class="col-md-2">
                        <select name="search_status" id="search-status" class="form-control"">
                            <option value="all">Tất cả</option>
                            @if(Session::has('search_status') && Session::get('search_status') == 'no_assign')
                            <option value="no_assign" selected="">Chưa phân công</option>
                            @else
                            <option value="no_assign">Chưa phân công</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search_shipper" id="search-shipper" class="form-control" value="{{ @Session::get('search_shipper') }}" placeholder="Tìm SDT/tên shipper">
                    </div>
                </div>
                @endif
                @if (isset($columns))
                    <div class="dataTables_wrapper">
                        <div class="table-scrollable">
                            <table id="{!! $id !!}" class="display table table-bordered">
                                <thead>
                                <tr>
                                    @foreach($columns as $lb)
                                        <th>{{$lb['title']}}</th>
                                    @endforeach
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                @else
                    <p style="text-align: center; font-size: 16px">Nothings to display...</p>
                @endif
            </div>
        </div>
    @else
        <table id="{!! $id !!}" class="display table table-bordered">
            <thead>
            <tr>
                @foreach($columns as $lb)
                    <th>{{$lb['title']}}</th>
                @endforeach
            </tr>
            </thead>

        </table>
    @endif
</div>
<?php
?>
@push('script')
    <script>
        $(document).ready(function () {
            var filter = $(".dataTables_filter").val();
            var id = '#{{$id}}';
            console.log(filter);
            var oTable = $(id).DataTable({
                order: [[ 0, "desc" ]],
                ajax: {
                    url: '{!! $url !!}',
                    type: '{{ $method or "GET"}}',
                    @if (isset($customSearch))
                    data: function(d){
                        console.log(d);
                        d.search_status = $('select[name=search_status]').val();
                        d.search_shipper = $('input[name=search_shipper]').val();
                    }
                    @endif
                },
                stateSave: true,
                // processing: true,
                serverSide: true,
                @if (isset($columns))
                columns: <?php echo json_encode($columns); ?>,
                @endif
            });

            $('#search-status').change(function() {
                oTable.draw();
            });
            $( "#search-shipper" ).change(function() {
                oTable.draw();
            });
        });
    </script>
@endpush
<style>
    .dataTables_filter{
        padding: 10px 0px 5px 0px !important;
    }
    .dataTables_filter label{
        font-size: 14px !important;
    }
    .dataTables_filter input{
        margin-left: 10px !important;
        border: 1px solid #c2cad8 !important;
        height: 30px !important;
        width: 200px !important;
    }
    .dataTables_length{
        padding: 10px 0px 5px 0px !important;
    }

    .dataTables_length label {
        font-size: 14px !important;
    }
    .dataTables_length select {
        width: 60px!important;
        height: 30px !important;
    }

</style>