@extends('layouts.adminlte')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Roles Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Roles</li>
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
                            <h3 class="card-title">Roles List</h3>
                            <div class="card-tools">
                                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create New Role
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="rolesTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Display Name</th>
                                            <th>Description</th>
                                            <th>Users</th>
                                            <th>Permissions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($roles as $role)
                                            <tr>
                                                <td>{{ $role->name }}</td>
                                                <td>{{ $role->display_name }}</td>
                                                <td>{{ $role->description ?: 'No description' }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $role->users->count() }} users
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        {{ count($role->permissions ?? []) }} permissions
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('roles.show', $role) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <a href="{{ route('roles.edit', $role) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" 
                                                              class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No roles found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($roles->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $roles->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
<script>
$(document).ready(function() {
    $('#rolesTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[0, "asc"]]
    });
});
</script>
@endpush
