<section class="contact">
    <div class="container">
        <div class="row">
            <div class="col-md-2 col-sm-12"></div>
            <div class="col-md-8 col-sm-12 title-large">
                <h1>Liên hệ</h1>
                <p>Để giải đáp thêm về thắc mắc hoặc cần hỗ trợ thêm. Quý khách vui lòng liên hệ với Ship668 theo
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
                        <span>246/47 Hòa Hưng, phường 13, quận 10, TP. Hồ Chí Minh, Việt Nam</span>
                    </li>
                    <li>
                        <i class="fas fa-phone cyan"></i>
                        <span>0396.504.701 - 09640.222.63</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope cyan"></i>
                        <span>hotro.ship668@gmail.com</span>
                    </li>
                    <li>
                        <i class="fas fa-globe cyan"></i>
                        <span><a href="https://ship668.com/" style="color: #4c96d7 ; text-decoration: none">https://ship668.com/</a> </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
