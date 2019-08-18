@extends('admin.app')

@section('title')
    Shipper
@endsection

@section('sub-title')
    vị trí trên bản đồ
@endsection

@section('content')
    <div class="row">
        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/shippers') !!}" class="btn btn-default"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Quay lại</a>
        </div>
        <div class="col-lg-12">
            <div style="min-height: 800px" id="map"></div>
        </div>
    </div>
@endsection
@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.1/socket.io.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script>

        // 'http://giaohang.hoanvusolutions.com.vn:8890'
        let map = null;
        let iconMaker = "{{asset('img/icon-motor.png')}}";
        var getUrl = window.location;
        var socket = io.connect(getUrl.origin + ':8890');
        let markers = [];
        socket.on('message', function (data) {
            var data = JSON.parse(data);
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/maps/')}}',
                data: {lat: data.lat, lng: data.lng, id: data.id}
            }).done(function (res) {
                console.log('socket.io listening from api');
                console.log(res);
                loadLocation(map, res);
            });
        });

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: new google.maps.LatLng(10.798049, 106.686857),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
        }
        
        function loadLocation(map, data) {
            var locations = [];
            if (data != null){
                locations = data;
            }

            var infowindow = new google.maps.InfoWindow({});

            // xóa vị trí cũ để hiển thị vị trí mới
            for (var i = 0; i < markers.length; i++ ) {
                markers[i].setMap(null);
            }
            markers.length = 0;

            for (var i = 0; i < locations.length; i++) {
                var makerLocation = new google.maps.LatLng(locations[i][1], locations[i][2]);

                var marker = new google.maps.Marker( {
                        icon: {
                            url: iconMaker,
                            // This marker is 20 pixels wide by 32 pixels high.
                            size: new google.maps.Size(32, 32),
                            // The origin for this image is (0, 0).
                            origin: new google.maps.Point(0, 0),
                            // The anchor for this image is the base of the flagpole at (0, 32).
                            anchor: new google.maps.Point(0, 32)
                        }, 
                        position: makerLocation, 
                        map: map
                    } );
                markers.push(marker);

                google.maps.event.addListener(marker, 'click', (function (marker, i) {
                    return function () {
                        infowindow.setContent(locations[i][0]);
                        infowindow.open(map, marker);
                    }
                })(marker, i));
            }
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCmH3h8ZX2KYFZ0SZW5UW1ra1jmXhJxVH0&callback=initMap"></script>
@endpush
