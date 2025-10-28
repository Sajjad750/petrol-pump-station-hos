@extends('layouts.adminlte')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Role Details: {{ $role->display_name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Role Information</h3>
                            <div class="card-tools">
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm mr-2">
                                    <i class="fas fa-edit"></i> Edit Role
                                </a>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Roles
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Role Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Role Information</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $role->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Display Name:</strong></td>
                                            <td>{{ $role->display_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description:</strong></td>
                                            <td>{{ $role->description ?: 'No description provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $role->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Updated:</strong></td>
                                            <td>{{ $role->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Statistics</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Assigned Users:</strong></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $role->users->count() }} users
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Permissions:</strong></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ count($role->permissions ?? []) }} permissions
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Assigned Users -->
                            <div class="mb-4">
                                <h5 class="mb-3">Assigned Users</h5>
                                @if($role->users->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($role->users as $user)
                                                    <tr>
                                                        <td>{{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No users assigned to this role.</p>
                                @endif
                            </div>

                            <hr>

                            <!-- Permissions -->
                            <div>
                                <h5 class="mb-3">Permissions</h5>
                                @if(count($role->permissions ?? []) > 0)
                                    <div class="row">
                                        @foreach($role->permissions as $permissionName)
                                            @php
                                                $permission = $permissions->firstWhere('name', $permissionName);
                                            @endphp
                                            @if($permission)
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="card border-success">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-check-circle text-success mr-2"></i>
                                                                <div>
                                                                    <div class="font-weight-bold text-success">
                                                                        {{ $permission->display_name }}
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        {{ $permission->description }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No permissions assigned to this role.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
