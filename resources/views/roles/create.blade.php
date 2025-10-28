@extends('layouts.adminlte')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Role</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active">Create</li>
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
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Roles
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('roles.store') }}">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Role Name -->
                                        <div class="form-group">
                                            <label for="name">Role Name <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   placeholder="e.g., admin, manager, operator"
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- Display Name -->
                                        <div class="form-group">
                                            <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   id="display_name" 
                                                   name="display_name" 
                                                   value="{{ old('display_name') }}"
                                                   class="form-control @error('display_name') is-invalid @enderror"
                                                   placeholder="e.g., Administrator, Manager, Operator"
                                                   required>
                                            @error('display_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" 
                                              name="description" 
                                              rows="3"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Describe the role's responsibilities and access level">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Permissions -->
                                <div class="form-group">
                                    <label>Permissions</label>
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           id="permission_{{ $permission->id }}" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}"
                                                           {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                                           class="form-check-input">
                                                    <label for="permission_{{ $permission->id }}" 
                                                           class="form-check-label">
                                                        {{ $permission->display_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('permissions')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Action Buttons -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('roles.index') }}" 
                                           class="btn btn-secondary mr-2">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <button type="submit" 
                                                class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Role
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
