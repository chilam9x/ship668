<section class="contact">
    <div class="container">
        <div class="row">
            <div class="col-md-2 col-sm-12"></div>
            <div class="col-md-8 col-sm-12 title-large">
                <h1>Liên hệ</h1>
                <p>Để giải đáp thêm về thắc mắc hoặc cần hỗ trợ thêm. Quý khách vui lòng liên hệ với Smart Express theo
                    thông tin dưới đây.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12 contact-form">
                {{ Form::open(['url' => 'front-ent/feedback', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                <input type="text" name="name" placeholder="Họ tên"/>
                @if ($errors->has('name'))
                    @foreach ($errors->get('name') as $error)
                        <div style="width: 100%" class="pull-right">
                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                        </div>
                    @endforeach
                @endif
                <input type="text" name="phone" placeholder="Số điện thoại"/>
                @if ($errors->has('phone'))
                    @foreach ($errors->get('phone') as $error)
                        <div style="width: 100%" class="pull-right">
                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                        </div>
                    @endforeach
                @endif
                <input type="text" name="email" placeholder="Email"/>
                @if ($errors->has('email'))
                    @foreach ($errors->get('email') as $error)
                        <div style="width: 100%" class="pull-right">
                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                        </div>
                    @endforeach
                @endif
                <textarea rows="5" name="contents" cols="80" placeholder="Nhập nội dung"></textarea>
                @if ($errors->has('contents'))
                    @foreach ($errors->get('contents') as $error)
                        <div style="width: 100%" class="pull-right">
                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                        </div>
                    @endforeach
                @endif
                <input type="submit" value="Gửi"/>
                {!! Form::close() !!}
            </div>
            <div class="col-md-6 col-sm-12 contact-infor">
                <ul>
                    <li>
                        <i class="fas fa-map-marker-alt cyan"></i>
                        <span>79 Chương Dương, Phường Linh Chiểu, Quận Thủ đức, TP. Hồ Chí Minh, Việt Nam</span>
                    </li>
                    <li>
                        <i class="fas fa-phone cyan"></i>
                        <span>(028) 22 419 555</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope cyan"></i>
                        <span>hotro.smartexpress@gmail.com</span>
                    </li>
                    <li>
                        <i class="fas fa-globe cyan"></i>
                        <span><a href="http://www.smartexpress.vn" style="color: #f15922; text-decoration: none">www.smartexpress.vn</a> </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
