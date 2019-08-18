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
            $(id).DataTable({
                order: [[ 0, "desc" ]],
                ajax: {
                    url: '{!! $url !!}',
                    type: '{{ $method or "GET"}}'
                },
                stateSave: true,
                // processing: true,
                @if (isset($columns))
                columns: <?php echo json_encode($columns); ?>,
                @endif
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