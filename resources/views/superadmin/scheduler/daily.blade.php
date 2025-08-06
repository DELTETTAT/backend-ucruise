<!-- start thisWeek calender-->
<div class="row" id="daily">
    <div class="col-12">
        <table class="table schedularTable">
            <tr>
                <th class="text-center" id="firstth"></th>
                <th class="text-center">
                    <h2>12</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>1</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>2</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>3</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>4</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>5</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>6</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>7</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>8</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>9</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>10</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>11</h2>AM<br />|
                </th>
                <th class="text-center">
                    <h2>12</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>1</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>2</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>3</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>4</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>5</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>6</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>7</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>8</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>9</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>10</h2>PM<br />|
                </th>
                <th class="text-center">
                    <h2>11</h2>PM<br />|
                </th>
            </tr>
            @php $currentDate = strtotime($dates[0]); @endphp
            @foreach($users_new as $user)

            <input type="hidden" name="user_ids[]" value="{{$user->id}}" id="user_ids">
            <tr id="{{$user->id}}_{{$currentDate}}">
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
                    <h5>{{$user->first_name.' '.$user->last_name}}</h5>
                    <p>180 Hours</p>

                </td>
                <!-- append area -->
            </tr>
            @endforeach
        </table>
    </div>
</div>
<!-- end thisWeek calender-->
