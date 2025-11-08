<aside class="sidebar" :class="{ 'collapsed': !sidebarOpen, 'expanded': sidebarOpen }" x-cloak>
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="{{ route('drives.index') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/tridah icon.png') }}" alt="Tridah" height="32" class="me-2">
            <span class="logo-text text-white fw-bold">Tridah</span>
        </a>
    </div>
    
    <!-- Create Drive Button -->
    <div class="px-3 mb-3">
        <a href="{{ route('drives.create') }}" class="btn btn-primary w-100">
            <i class="fas fa-plus me-2"></i>New Drive
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <p class="nav-section-title text-muted text-uppercase small mb-3">Main</p>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('drives.index') }}" class="nav-link {{ request()->routeIs('drives.index') ? 'active' : '' }}">
                        <i class="fas fa-hdd me-2"></i>
                        <span>My Drives</span>
                    </a>
                </li>
                @auth
                    @php
                        $personalDrive = auth()->user()->getPersonalDrive();
                        $isPersonalActive = request()->routeIs('drives.show') && request()->route('drive') && request()->route('drive')->id == $personalDrive->id;
                        $personalIconColor = $personalDrive->color ?? '#31d8b2';
                        $personalIconName = $personalDrive->icon ?? 'user';
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('drives.show', $personalDrive) }}" class="nav-link {{ $isPersonalActive ? 'active' : '' }}">
                            <i class="fas fa-{{ $personalIconName }} me-2" style="color: {{ $personalIconColor }};"></i>
                            <span>Personal Drive</span>
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
        
        @auth
            @php
                // Get only parent drives (non-sub drives) and limit to 5
                $parentDrives = auth()->user()->sharedDrives()
                    ->whereNull('parent_drive_id')
                    ->with('subDrives')
                    ->limit(5)
                    ->get();
            @endphp
            @if($parentDrives->count() > 0)
            <div class="nav-section mt-4">
                <p class="nav-section-title text-muted text-uppercase small mb-3">Shared Drives</p>
                <ul class="nav flex-column">
                    @foreach($parentDrives as $drive)
                        @php
                            $isActive = request()->routeIs('drives.show') && request()->route('drive') && request()->route('drive')->id == $drive->id;
                            $iconColor = $drive->color ?? '#31d8b2';
                            $iconName = $drive->icon ?? 'folder-open';
                        @endphp
                        <li class="nav-item">
                            <a href="{{ route('drives.show', $drive) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                                <i class="fas fa-{{ $iconName }} me-2" style="color: {{ $iconColor }};"></i>
                                <span>{{ Str::limit($drive->name, 20) }}</span>
                                @if($drive->getUserRole(auth()->user()) === 'owner')
                                    <span class="badge bg-brand-teal ms-auto">Owner</span>
                                @endif
                            </a>
                        </li>
                        
                        {{-- Show sub-drives indented --}}
                        @if($drive->subDrives->count() > 0)
                            @foreach($drive->subDrives as $subDrive)
                                @php
                                    $isSubActive = request()->routeIs('drives.show') && request()->route('drive') && request()->route('drive')->id == $subDrive->id;
                                    $subIconColor = $subDrive->color ?? '#31d8b2';
                                    $subIconName = $subDrive->icon ?? 'folder';
                                @endphp
                                <li class="nav-item" style="padding-left: 1.5rem;">
                                    <a href="{{ route('drives.show', $subDrive) }}" class="nav-link {{ $isSubActive ? 'active' : '' }}">
                                        <i class="fas fa-{{ $subIconName }} me-2" style="color: {{ $subIconColor }};"></i>
                                        <span>{{ Str::limit($subDrive->name, 20) }}</span>
                                        <span class="badge bg-info ms-auto" style="font-size: 0.6rem;">Sub</span>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
        @endauth
        
        <div class="nav-section mt-4">
            <p class="nav-section-title text-muted text-uppercase small mb-3">Account</p>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user-circle me-2"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Footer -->
    <div class="sidebar-footer mt-auto">
        <div class="nav-section">
            <div class="text-center mb-2">
                <small class="text-muted">Powered by Tridah</small>
            </div>
            <div class="d-flex justify-content-center gap-2">
                <a href="https://tridah.cloud" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-globe"></i>
                </a>
                <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>
    </div>
</aside>
