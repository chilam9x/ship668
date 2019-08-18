<div class="modal fade" id="searchBooking" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 800px !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Thông tin đơn hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <section class="sub-content">
                    <div class="container">
                        <div class="row sub-title">
                            <div class="col-md-12 col-sm-12">
                                <h3>Người gửi</h3>
                                <div class="line"></div>
                            </div>
                        </div>
                        <div class="row order-form">
                            <div class="col-md-12 col-sm-12">
                                <ul>
                                    <li>
                                        <label>Số điện thoại:</label>
                                        <input name="phone_number_fr" type="text" readonly/>
                                    </li>
                                    <li>
                                        <label>Tên người gửi</label>
                                        <input name="name_fr" type="text" readonly/>
                                    </li>
                                    <li>
                                        <label>Địa chỉ</label>
                                        <input name="address_fr" type="text" readonly/>
                                    </li>
                                    <li>
                                        <label>Hình thức gửi hàng:</label>
                                        <input name="receive_type" type="text" readonly/>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row sub-title">
                            <div class="col-md-12 col-sm-12">
                                <h3>Người nhận</h3>
                                <div class="line"></div>
                            </div>
                        </div>
                        <div class="row order-form">
                            <div class="col-md-12 col-sm-12">
                                <ul>
                                    <li>
                                        <label>Số điện thoại:</label>
                                        <input type="text" name="phone_number_to" readonly placeholder="Số điện thoại"/>
                                    </li>
                                    <li>
                                        <label>Tên người nhận</label>
                                        <input type="text" name="name_to" readonly placeholder="Tên người nhận"/>
                                    </li>
                                    <li>
                                        <label>Địa chỉ</label>
                                        <input type="text" name="address_to" readonly/>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row sub-title">
                            <div class="col-md-12 col-sm-12">
                                <h3>Thông tin đơn hàng</h3>
                                <div class="line"></div>
                            </div>
                        </div>
                        <div class="row order-form">
                            <div class="col-md-12 col-sm-12">
                                <ul id="ui_search_booking">
                                    <li>
                                        <label>Tên hàng hóa:</label>
                                        <input type="text" name="name" readonly/>
                                    </li>
                                    <li>
                                        <label>Mã đơn hàng:</label>
                                        <input type="text" name="uuid" readonly/>
                                    </li>
                                    <li>
                                        <label>Trạng thái đơn hàng:</label>
                                        <input type="text" name="status" readonly/>
                                    </li>
                                    <li>
                                        <label>Khối lượng:</label>
                                        <input type="text" name="weight" readonly/>
                                    </li>
                                    <li>
                                        <label>Hình thức gửi hàng:</label>
                                        <input type="text" name="transport_type" readonly/>
                                    </li>
                                    <li>
                                        <label>Số tiền thu hộ:</label>
                                        <input type="text" name="cod" readonly/>
                                    </li>
                                    <li>
                                        <label>Ghi chú bắt buộc:</label>
                                        <input type="text" name="payment_type" readonly/>
                                    </li>
                                    <li>
                                        <label>Ghi chú khác:</label>
                                        <input type="text" name="other_note" readonly/>
                                    </li>
                                    <li>
                                        <label>Ghi chú hệ thống:</label>
                                        <input type="text" name="note" readonly/>
                                    </li>
                                    <li>
                                        <label>Cước phí:</label>
                                        <input type="text" name="price" readonly/>
                                    </li>
                                    <li>
                                        <label>Số tiền đã thanh toán:</label>
                                        <input type="text" name="paid" readonly/>
                                    </li>
                                    <li>
                                        <label>Chi phí phát sinh:</label>
                                        <input type="text" name="incurred" readonly/>
                                    </li>
                                    <li>
                                        <label>Ngày tạo:</label>
                                        <input type="text" name="created_at" readonly/>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
