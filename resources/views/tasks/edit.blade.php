@extends('layouts.dashboard')

@section('title', 'Edit Task - ' . $drive->name)

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}">{{ $project->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}">{{ $task->title }}</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Task</h4>
                    </div>
                    <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @include('tasks.partials.form')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.getElementById('descriptionEditor');
    if (!editorElement) {
        return;
    }

    const quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ],
        },
    });

    const hiddenInput = document.getElementById('description');
    const initialContent = {!! json_encode(old('description', $task->description)) !!} || '';
    if (initialContent) {
        quill.root.innerHTML = initialContent;
        hiddenInput.value = initialContent;
    }

    const form = document.getElementById('taskForm');
    form.addEventListener('submit', function () {
        hiddenInput.value = quill.root.innerHTML.trim();
    });

});
</script>
@endpush
