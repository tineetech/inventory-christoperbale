@extends('layouts.auth')

@section('content')

<div class="page-loader">
    <div class="bg-primary"></div>
</div>

<div class="authentication-wrapper authentication-1 px-4">
    <div class="authentication-inner bg-white shadow py-5" style="padding-inline: 50px">

        <!-- Logo -->
        <div class="d-flex justify-content-center align-items-center">
            <div class="ui-w-60">
                <div class="w-100 position-relative">
                    <img src="{{ asset('assets/img/logo-inv.png') }}" class="img-fluid">
                </div>
            </div>
        </div>

        <!-- FORM -->
        <form class="my-5" method="POST" action="{{ route('login.action') }}">
            @if(session('error'))
            <div class="alert alert-danger bg-red-600 alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success bg-green-600 alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif
            @csrf

            <div class="form-group">
                <label class="form-label">Email / Username</label>
                <input type="text" name="email" class="form-control" required>
            </div>

            <div class="form-group position-relative">
                <label class="form-label">Password</label>

                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>

                    <div class="input-group-append">
                        <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                            <i class="feather icon-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-between align-items-center m-0">
                <label class="custom-control custom-checkbox m-0">
                    <input type="checkbox" class="custom-control-input">
                    <span class="custom-control-label">Remember me</span>
                </label>

                <button type="submit" class="btn btn-primary">
                    Sign In
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function togglePassword() {

    let password = document.getElementById("password");
    let icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("icon-eye");
        icon.classList.add("icon-eye-off");
    } else {
        password.type = "password";
        icon.classList.remove("icon-eye-off");
        icon.classList.add("icon-eye");
    }

}
</script>

@endsection