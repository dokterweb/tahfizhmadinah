@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-md-4">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Profile</h3>
                </div>

                <div class="card-body text-center">

                    @if($user->avatar)
                        <img
                            src="{{ asset('storage/' . $user->avatar) }}"
                            alt="Avatar"
                            class="rounded-circle mb-3"
                            width="120"
                            height="120"
                            style="object-fit: cover;"
                        >
                    @else
                        <div
                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:120px;height:120px;font-size:40px;"
                        >
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <h3 class="mb-1">
                        {{ $user->name }}
                    </h3>

                    <div class="text-muted">
                        {{ $user->email }}
                    </div>

                    @if($user->roles->count())
                        <div class="mt-2">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                </div>

            </div>

        </div>


        <div class="col-md-8">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        Edit Profile
                    </h3>
                </div>

                <form
                    action="{{ route('profile.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >

                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif


                        {{-- Name --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Nama
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                            >

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Email --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Avatar --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Avatar
                            </label>

                            <input
                                type="file"
                                name="avatar"
                                class="form-control @error('avatar') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small class="text-muted">
                                JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                            </small>

                            @error('avatar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <hr>

                        <h4>
                            Ganti Password
                        </h4>

                        <p class="text-muted">
                            Kosongkan jika tidak ingin mengganti password.
                        </p>


                        {{-- Password --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Password Baru
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                            >

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Confirm Password --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Konfirmasi Password Baru
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                            >

                        </div>

                    </div>


                    <div class="card-footer text-end">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection