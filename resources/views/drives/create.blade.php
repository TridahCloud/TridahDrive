@extends('layouts.dashboard')

@section('title', 'Create Shared Drive')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="dashboard-card">
                <h2 class="mb-4 brand-teal">Create New Shared Drive</h2>
                
                <form action="{{ route('drives.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Drive Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', '#31d8b2') }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <select class="form-select @error('icon') is-invalid @enderror" id="icon" name="icon">
                                <option value="folder">Folder</option>
                                <option value="briefcase">Briefcase</option>
                                <option value="building">Building</option>
                                <option value="users">Team</option>
                                <option value="project-diagram">Project</option>
                                <option value="folder-open">Open Folder</option>
                            </select>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">Icon Preview:</small>
                                <div class="d-flex gap-2 flex-wrap" id="icon-selector">
                                    <button type="button" class="icon-option-btn" data-icon="folder" title="Folder">
                                        <i class="fas fa-folder"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="briefcase" title="Briefcase">
                                        <i class="fas fa-briefcase"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="building" title="Building">
                                        <i class="fas fa-building"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="users" title="Team">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="project-diagram" title="Project">
                                        <i class="fas fa-project-diagram"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="folder-open" title="Open Folder">
                                        <i class="fas fa-folder-open"></i>
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Selected: </small>
                                    <span id="icon-preview" style="font-size: 1.5rem;">
                                        <i class="fas fa-folder" id="preview-icon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('drives.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Drive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.icon-option-btn {
    width: 48px;
    height: 48px;
    border: 2px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.icon-option-btn:hover {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.1);
    color: #31d8b2;
    transform: scale(1.1);
}

.icon-option-btn.active {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.2);
    color: #31d8b2;
}

[data-theme="light"] .icon-option-btn {
    background: #ffffff;
    border-color: #dee2e6;
    color: #495057;
}

[data-theme="light"] .icon-option-btn:hover {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.1);
    color: #31d8b2;
}

[data-theme="light"] .icon-option-btn.active {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.15);
    color: #31d8b2;
}
</style>
@endpush

@push('scripts')
<script>
    const iconSelect = document.getElementById('icon');
    const colorInput = document.getElementById('color');
    const previewIcon = document.getElementById('preview-icon');
    const iconButtons = document.querySelectorAll('.icon-option-btn');
    
    const iconMap = {
        'folder': 'fa-folder',
        'briefcase': 'fa-briefcase',
        'building': 'fa-building',
        'users': 'fa-users',
        'project-diagram': 'fa-project-diagram',
        'folder-open': 'fa-folder-open'
    };
    
    function updateIconPreview() {
        const selectedIcon = iconSelect.value;
        const iconClass = iconMap[selectedIcon] || 'fa-folder';
        previewIcon.className = 'fas ' + iconClass;
        previewIcon.style.color = colorInput.value;
        
        // Update active state on buttons
        iconButtons.forEach(btn => {
            if (btn.dataset.icon === selectedIcon) {
                btn.classList.add('active');
                btn.style.color = colorInput.value;
                btn.style.borderColor = colorInput.value;
            } else {
                btn.classList.remove('active');
                btn.style.color = '';
                btn.style.borderColor = '';
            }
        });
    }
    
    iconSelect.addEventListener('change', updateIconPreview);
    colorInput.addEventListener('input', updateIconPreview);
    
    // Icon button click handlers
    iconButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            iconSelect.value = this.dataset.icon;
            updateIconPreview();
        });
    });
    
    // Initialize preview
    updateIconPreview();
</script>
@endpush
@endsection

