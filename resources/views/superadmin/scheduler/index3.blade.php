<?php error_reporting(0);
@$flag = @$flag;
//  echo $flag;die;
?>
<style>
    .lightred {
        background-color: #f2dbdb !important;
    }
</style>

<!-- start thisWeek calender-->
<div class="row" id="thisWeek" style="display: none1">
    <div class="col-12">
        <table class="table schedularTable">
            <tr>
                <th class="text-center"></th>
                @foreach ($days as $dData)

                <th class="text-center">
                    <span class="activeday">
                        <h2>{{date('d',strtotime($dData['date']))}}</h2>
                    </span>{{strtoupper($dData['day'])}}<br />|

                </th>

                @endforeach

            </tr>

            @foreach($users_new as $user)

            <input type="hidden" name="user_ids[]" value="{{$user->id}}" id="user_ids">

            @php $currentDate = strtotime($days[0]['date']); @endphp

            <tr>
                <td class="user-detail">
                    <img src="{{asset('assets/images/user.png')}}" alt="user-image" class="rounded-circle">
                    <div class="btn-group float-right more">
                        <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="{{asset('assets/images/more.png')}}" alt="menu">
                        </button>
                        <!-- <div class="dropdown-menu">
                                <a class="dropdown-item" href="#"></a>
                            </div> -->
                    </div>
                    <h5>{{@$user->first_name.' '.@$user->last_name}} </h5>
                    <p>180 Hours</p>

                </td>

                @while ($currentDate <= $endDate) <td class="w13" id="{{$user->id}}_{{$currentDate}}" data-count="0">
                    <!-- <div class="card-cal lightred">
                            No data available!
                        </div> -->
                    </td>

                    @php $currentDate = strtotime("+1 day", $currentDate); @endphp
                    @endwhile

            </tr>
            @endforeach

        </table>
    </div>
</div>
<!-- end thisWeek calender-->



<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<script>
    var url = "<?= url('/'); ?>";
    // alert(url);

    // function renderUserData(days, dates, months, years, user_ids){

    //     var formData = {
    //         _token : "{{ csrf_token() }}",
    //         days : days,
    //         dates : dates,
    //         months : months,
    //         years : years,
    //         user_ids : user_ids,
    //     };

    //     var type = "POST";
    //     var ajaxurl = "{{route('getweeklyScheduleInfo')}}";

    //     $.ajax({
    //         type: type,
    //         url: ajaxurl,
    //         data: formData,
    //         dataType: 'json',
    //         success: function (data) {
    //             $.each(data.schedule, function(k, v) {
    //                 $.each(v.clients, function(k1, v1) {
    //                     var p1 = '<td class="w13">';
    //                     var p2 = '<div class="card-cal lightgreen">';
    //                     var p3 = '<h5>'+v1.user.first_name+'</h5>';
    //                     var p4 = '<p>{{date("H:i A",strtotime('+v.start_time+'))}} - {{date("H:i A",strtotime('+v.end_time+'))}}</p>';
    //                     var p5 = '<hr class="line" />';
    //                     var p6 = '<p class="bold">Social support</p>';
    //                     var p7 = '<div class="soical-icon mt-2">';
    //                     var p8 = '<img src="{{asset("assets/images/Group.svg")}}" alt="Group" class="icon">';
    //                     var p9 = '<img src="{{asset("assets/images/money1.svg")}}" alt="money1" class="icon">';
    //                     var p10 = '<img src="{{asset("assets/images/money2.svg")}}" alt="money2" class="icon">';
    //                     var p11 = '<img src="{{asset("assets/images/money-recive.svg")}}" alt="money-recive" class="float-right soical-recive">';
    //                     var p12 = '</div>';
    //                     var p13 = '</div>';
    //                     var p14 = '</td>';
    //                     $("#"+v1.client_id+"_"+(Date.parse(v.date) / 1000)).html(p1+p2+p3+p4+p5+p6+p7+p8+p9+p10+p11+p12+p13+p14);
    //                 });
    //             });
    //         },
    //         error: function (data) {
    //             console.log(data);
    //         }
    //     });

    // }

    // $(document).ready(function() {

    //        var days = {!! json_encode($days) !!};
    //        var dates = {!! json_encode($dates) !!};
    //        var months = {!! json_encode($months) !!};
    //        var years = {!! json_encode($years) !!};
    //        var user_ids = $("input[name='user_ids[]']").map(function(){return $(this).val();}).get();

    //        renderUserData(days, dates, months, years, user_ids);
    // });
</script>