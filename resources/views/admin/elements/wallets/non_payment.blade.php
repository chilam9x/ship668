@extends('admin.app')

@section('title')
    Rút tiền
@endsection

@section('sub-title')
    Chưa thanh toán
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="well" style="padding-left: 0px">
            <div class="row">
                <form action="{!! url('admin/booking/exportAdvance') !!}" method="get">
                    <input type="hidden" name="status[]" value="new">
                    <input type="hidden" name="status[]" value="taking">
                    <input type="hidden" name="sub_status[]" value="none">
                    <div class="col-lg-8">
                        <div class="input-group">
                             <span class="input-group-addon" id="sizing-addon2"><span
                                         class="glyphicon glyphicon-calendar"> </span> Từ ngày</span>
                            <input type="date" id="date_from" name="date_from" class="form-control"
                                   aria-describedby="sizing-addon2" value="{!! $time_from !!}">
                            <span class="input-group-addon" id="sizing-addon2"><span
                                        class="glyphicon glyphicon-calendar"> </span> Đến ngày</span>
                            <input type="date" id="date_to" name="date_to" class="form-control"
                                   aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            <span class="input-group-addon">Số điện thoại</span>
                            <input style="min-width: 180px" type="text" id="phone" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12" style="margin-top: 5px">
                        <div class="row">
                            <div class="col-lg-12" style="margin-top: 5px">
                                <button type="button" id="quick-assign" class="btn btn-circle btn-primary">
                                    Thanh toán hàng loạt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'wallets',
               'title' => [
                       'caption' => 'Yêu cầu rút tiền chưa thanh toán',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/wallet/non-payment"),
               'columns' => [
                       ['data' => 'created_at', 'title' => 'Ngày rút'],
                       ['data' => 'customer_name', 'title' => 'Người rút tiền'],
                       ['data' => 'customer_phone_number', 'title' => 'Số ĐT'],
                       ['data' => 'bank_account', 'title' => 'Tên TK ngân hàng', 'orderable' => false],
                       ['data' => 'bank_account_number', 'title' => 'Số TK ngân hàng', 'orderable' => false],
                       ['data' => 'bank_name', 'title' => 'Tên ngân hàng', 'orderable' => false],
                       ['data' => 'bank_branch', 'title' => 'Chi nhánh ngân hàng', 'orderable' => false],
                       ['data' => 'price', 'title' => 'Số tiền'],
                       ['data' => 'payment_code', 'title' => 'Mã số rút tiền'],
                       ['data' => 'action', 'title' => 'Hình thức thanh toán', 'orderable' => false]
                   ]
               ])
        </div>
    </div>

    <!-- Modal -->
    <form action="" method="POST" id="form-quick-assign">
        {!! csrf_field() !!}
        <div class="modal fade" id="quickAssignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Thanh toán hàng loạt</h4>
              </div>
              <div class="modal-body" style="max-height: 450px; overflow-y: scroll;" >
                <div class="row">
                    <div class="col-md-12">
                        <div style="text-align: right; margin-bottom: 10px">
                            Hình thức thanh toán: 
                            <input type="radio" name="withdrawal_type" value="cash"> Tiền mặt &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="withdrawal_type" value="transfer" checked=""> Chuyển khoản
                        </div>
                        <table id="ul-book" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="check-all"></th>
                                    <th>Ngày rút</th>
                                    <th>Người rút</th>
                                    <th>Số ĐT</th>
                                    <th>Tên TK</th>
                                    <th>Số TK</th>
                                    <th>Tên ngân hàng</th>
                                    <th>Tên chi nhánh</th>
                                    <th>Số tiền</th>
                                    <th>Mã số</th>
                                </tr>
                            </thead>
                            <tbody style="max-height: 400px; overflow-y: scroll;"></tbody>
                        </table>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <span id="msg-error" style="color: red"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Thoát</button>
                <button type="button" class="btn btn-primary" id="save-quick-assign">Đồng ý</button>
              </div>
            </div>
          </div>
        </div>
    </form>
@endsection

@push('script')
    <script>
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'showImageNumberLabel': true,
        });

        function loadListBoook() {
            $.ajax({
                type: "GET",
                url: "{{ route('wallet.get_quick_assign') }}",
                data: {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    phone: $('#phone').val()
                },
                dataType: "JSON"
            }).done(function (msg) {
                console.log(msg);
                $('#quickAssignModal #ul-book tbody').html('');
                if (msg.length > 0) {
                    $( msg ).each(function( index, value ) {
                        var bookLi = '';
                        bookLi += '<tr>';
                        bookLi += '<td><input type="checkbox" value="' + value.id + '" name="wallets"></td>';
                        bookLi += '<td>' + value.created_at + '</td>';
                        if (value.customer_name) {
                            bookLi += '<td>' + value.customer_phone_number + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        if (value.customer_phone_number) {
                            bookLi += '<td>' + value.customer_phone_number + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        if (value.user.bank_account) {
                            bookLi += '<td>' + value.user.bank_account + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        if (value.user.bank_account_number) {
                            bookLi += '<td>' + value.user.bank_account_number + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        if (value.user.bank_name) {
                            bookLi += '<td>' + value.user.bank_name + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        if (value.user.bank_branch) {
                            bookLi += '<td>' + value.user.bank_branch + '</td>';
                        } else {
                            bookLi += '<td></td>';
                        }
                        bookLi += '<td>' + value.price + '</td>';
                        bookLi += '<td>' + value.payment_code + '</td>';
                        bookLi += '</tr>';
                        $('#quickAssignModal #ul-book tbody').append(bookLi);
                    });
                }
                $('#quickAssignModal').modal('show');
            });
        }

        $(document).ready(function(){
            $("#quick-assign").click(function(){
                loadListBoook();
            });

            $('#save-quick-assign').click(function(e){
                $.ajax({
                    type: "POST",
                    url: "{{ route('wallet.post_quick_assign') }}",
                    data: {
                        inputs: $('#form-quick-assign').serializeArray(),
                        _token: $("input[name='_token']").val()
                    },
                    dataType: "JSON"
                }).done(function (msg) {
                    if (msg.status == 'success') {
                        $('#quickAssignModal').modal('hide');
                        location.reload();
                    } else {
                        $('#msg-error').html(msg.status);
                    }
                });
            })

            $('#check-all').change(function(){
                var checkboxes = $(this).closest('form').find(':checkbox');
                if($(this).prop('checked')) {
                  checkboxes.prop('checked', true);
                } else {
                  checkboxes.prop('checked', false);
                }
            });
        });

    </script>
@endpush