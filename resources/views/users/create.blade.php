@extends('layouts.adminlte')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">Create User</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" name="password_confirmation" required>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Roles</label>
                                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                            @forelse ($roles as $role)
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="role_{{ $role->id }}" 
                                                           name="roles[]" 
                                                           value="{{ $role->id }}"
                                                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="role_{{ $role->id }}">
                                                        <strong>{{ $role->display_name ?? $role->name }}</strong>
                                                        @if ($role->description)
                                                            <br>
                                                            <small class="text-muted">{{ $role->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @empty
                                                <p class="text-muted">No roles available</p>
                                            @endforelse
                                        </div>
                                        <small class="form-text text-muted">Select one or more roles</small>
                                        @error('roles')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Direct Permissions</label>
                                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                            @forelse ($permissions as $permission)
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="permission_{{ $permission->id }}" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                        <strong>{{ $permission->display_name ?? $permission->name }}</strong>
                                                        @if ($permission->description)
                                                            <br>
                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @empty
                                                <p class="text-muted">No permissions available</p>
                                            @endforelse
                                        </div>
                                        <small class="form-text text-muted">Select specific permissions for this user</small>
                                        @error('permissions')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create User
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

