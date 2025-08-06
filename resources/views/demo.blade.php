<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

</head>

<body>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-3"></div>
                <div class="col-6">
                    <h3>Roles</h3>

                    <form action="{{route('managePermissions.store')}}" method="POST">
                        @csrf
                        <input type="submit" value="Save">

                        @foreach($roles as $role)

                        <h5>{{$role->name}}</h5>

                        <table class="table">
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <th scope="row">
                                        <input class="form-check-input" type="checkbox" value="{{$permission->id}}" name="permissions_{{$role->id}}[]" id="flexCheckChecked">
                                    </th>
                                    <td>{{$permission->name}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endforeach
                    </form>

                    <!-- <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <th scope="row">{{$loop->index+1}}</th>
                                <td>{{$role->name}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table> -->
                </div>
                <div class="col-3"></div>
            </div>
        </div>
    </section>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</body>

</html>