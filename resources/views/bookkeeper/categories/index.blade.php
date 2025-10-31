@extends('layouts.dashboard')

@section('title', 'Categories - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}">BookKeeper</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-tags me-2"></i>Categories
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.bookkeeper.categories.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Category
                    </a>
                    <a href="{{ route('drives.bookkeeper.transactions.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to BookKeeper
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Categories Grid -->
    <div class="row">
        @forelse($categories as $category)
            <div class="col-md-4 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="category-color" style="width: 20px; height: 20px; background-color: {{ $category->color }}; border-radius: 4px; margin-right: 10px;"></div>
                        <h5 class="mb-0">{{ $category->name }}</h5>
                    </div>
                    @if($category->description)
                        <p class="text-muted small">{{ Str::limit($category->description, 100) }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            @if($category->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('drives.bookkeeper.categories.show', [$drive, $category]) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('drives.bookkeeper.categories.edit', [$drive, $category]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('drives.bookkeeper.categories.destroy', [$drive, $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-tags fa-3x mb-3 text-muted"></i>
                    <p class="text-muted">No categories found. <a href="{{ route('drives.bookkeeper.categories.create', $drive) }}">Create your first category</a></p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

