@extends('layouts.adminlte')

@section('content')
<div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users Managment</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">HOS Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid p-4">
        <!-- Stats row -->
        <div class="row mb-4">
            <div class="col mt-4">
                <div class="card shadow-sm p-3 mb-2 bg-white rounded text-center">
                    <div class="text-muted small">Total Users</div>
                    <div class="h4">{{ $totalUsers }}</div>
                </div>
            </div>
            <div class="col mt-4">
                <div class="card shadow-sm p-3 mb-2 bg-white rounded text-center">
                    <div class="text-muted small">Active</div>
                    <div class="h4">{{ $totalUsers }}</div>
                </div>
            </div>
            <div class="col mt-4">
                <div class="card shadow-sm p-3 mb-2 bg-white rounded text-center">
                    <div class="text-muted small">Inactive</div>
                    <div class="h4">{{ $totalUsers }}</div>
                </div>
            </div>
            <div class="col mt-4">
                <div class="card shadow-sm p-3 mb-2 bg-white rounded text-center">
                    <div class="text-muted small">Admins</div>
                    <div class="h4 text-primary">{{ $adminUsers }}</div>
                </div>
            </div>
        </div>

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
                        <table class="table table-hover align-middle">
                            <thead class="border-b">
                                <tr>
                                    <th>Name & Email</th>
                                    <th>Last Login</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </td>
                                    <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        @if($user->role)
                                            <span class="badge"
                                                style="">
                                                {{ ucfirst($user->role->display_name ?? $user->role->name) }}
                                            </span>
                                        @else
                                            <span class="text-muted">No role</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_active ?? true)
                                            <span class="badge bg-success-light text-success">active</span>
                                        @else
                                            <span class="badge bg-secondary-light text-secondary">inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm text-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm text-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No users found</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                        <div class="mt-3">{{ $users->links() }}</div>
                    </div>
                </div>

                <!-- Role Permissions Legend -->
                <div class="row mt-4 g-3">
                  <div class="col">
                    <div class="p-3 rounded border text-center" style="background:#f6faff; border-color:#d6bbfb; min-height:140px;">
                        <div class="mb-2 fw-semibold" style="color:#7a3e9d;">Super Admin</div>
                        <ul class="list-unstyled small mb-0 text-start mx-auto" style="max-width:18em;">
                            <li><b>Full system access</b></li>
                            <li>User management</li>
                            <li>Price changes</li>
                            <li>All reports & operations</li>
                        </ul>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3 rounded border text-center" style="background:#f8f9fe; border-color:#c7d7fe; min-height:140px;">
                        <div class="mb-2 fw-semibold" style="color:#3538cd;">Admin</div>
                        <ul class="list-unstyled small mb-0 text-start mx-auto" style="max-width:18em;">
                            <li>All reports & operations</li>
                            <li>Price changes</li>
                            <li>Site management</li>
                        </ul>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3 rounded border text-center" style="background:#ecfdf3; border-color:#d1fadf; min-height:140px;">
                        <div class="mb-2 fw-semibold" style="color:#027a48;">Manager</div>
                        <ul class="list-unstyled small mb-0 text-start mx-auto" style="max-width:18em;">
                            <li>View all reports</li>
                            <li>Operations monitoring</li>
                            <li>Cannot change prices</li>
                            <li>Cannot manage users</li>
                        </ul>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3 rounded border text-center" style="background:#fff8ec; border-color:#fef0c7; min-height:140px;">
                        <div class="mb-2 fw-semibold" style="color:#b54708;">Operator</div>
                        <ul class="list-unstyled small mb-0 text-start mx-auto" style="max-width:18em;">
                            <li>Limited reports access</li>
                            <li>View operations</li>
                            <li>No admin access</li>
                        </ul>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3 rounded border text-center" style="background:#f2f4f7; border-color:#e4e7ec; min-height:140px;">
                        <div class="mb-2 fw-semibold" style="color:#344054;">Viewer</div>
                        <ul class="list-unstyled small mb-0 text-start mx-auto" style="max-width:18em;">
                            <li>Read-only</li>
                            <li>View dashboards</li>
                            <li>No admin privileges</li>
                        </ul>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
@endsection

