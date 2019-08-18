<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<!-- <script src="{{asset('/assets/global/plugins/pusher.min.js')}}"></script> -->
<script>
    // $(document).ready(function(){
    //     var userId = '';
    //     var userId = '{{@Auth::user()->id}}';
    //     var pusher = new Pusher('dcad7c34effbe5194bf6', {
    //         cluster: 'ap1',
    //         encrypted: true
    //         // forceTLS: true
    //     });
    //     var notificationChannel = pusher.subscribe('notification-channel');
    //     notificationChannel.bind('App\\Events\\NotificationPusherEvent', function(data) {
    //         console.log(data.message);
    //         if (userId.length > 0) {
    //             $( data.message ).each(function( index, value ) {
    //                 if (value.user_id == userId) {
    //                     console.log(value);
    //                 }
    //             });
    //         }
    //     });
    // });
</script>
@stack('script')