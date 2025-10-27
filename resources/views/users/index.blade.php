@extends('layouts.adminlte')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Filters Card -->
                <div class="card custom-card mb-3">
                    <div class="card-header custom-card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('users.index') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search">Search</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               placeholder="Name or Email" value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select class="form-control" id="role" name="role">
                                            <option value="">All Roles</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->display_name ?? $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 d-flex align-items-end" style="gap: 10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                    <a href="{{ route('users.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add User
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table Card -->
                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">Users</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Direct Permissions</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @forelse ($user->roles as $role)
                                                    <span class="badge badge-primary">{{ $role->display_name ?? $role->name }}</span>
                                                @empty
                                                    <span class="text-muted">No roles</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                @forelse ($user->permissions as $permission)
                                                    <span class="badge badge-info">{{ $permission->display_name ?? $permission->name }}</span>
                                                @empty
                                                    <span class="text-muted">No direct permissions</span>
                                                @endforelse
                                            </td>
                                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if ($user->id !== auth()->id())
                                                        <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                                              onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No users found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

