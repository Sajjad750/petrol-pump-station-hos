@extends('layouts.adminlte')

@section('content')
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">User Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">ID</th>
                                        <td>{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Assigned Roles</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($user->role)
                                            <div class="mb-2">
                                                <span class="badge badge-primary badge-lg">{{ $user->role->display_name ?? $user->role->name }}</span>
                                                @if ($user->role->description)
                                                    <small class="text-muted d-block">{{ $user->role->description }}</small>
                                                @endif
                                                
                                                @if (!empty($user->role->permissions))
                                                    <div class="mt-1 ml-3">
                                                        <small class="text-muted">Permissions via this role:</small><br>
                                                        @foreach ($user->role->permissions as $permission)
                                                            <span class="badge badge-light badge-sm">{{ $permission }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-muted">No role assigned</p>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

