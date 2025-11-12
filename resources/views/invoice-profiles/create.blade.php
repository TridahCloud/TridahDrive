@extends('layouts.dashboard')

@section('title', 'Create Invoice Profile - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.invoices.index', $drive) }}">Invoices</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.invoice-profiles.index', $drive) }}">Invoice Profiles</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create Invoice Profile</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <a href="{{ route('drives.invoice-profiles.index', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.invoice-profiles.store', $drive) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Profile Information</h4>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Profile Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Personal, Business, Organization">
                        <small class="text-muted">A descriptive name for this profile</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Set as default profile</label>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Company Information</h4>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Your Company Name">
                    </div>

                    <div class="mb-3">
                        <label for="company_address" class="form-label">Address</label>
                        <textarea class="form-control" id="company_address" name="company_address" rows="2" placeholder="Street, City, State, ZIP">{{ old('company_address') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" value="{{ old('company_phone') }}" placeholder="+1 (555) 123-4567">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email" value="{{ old('company_email') }}" placeholder="info@company.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="company_website" name="company_website" value="{{ old('company_website') }}" placeholder="https://www.company.com">
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Company Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Upload a logo image (JPG, PNG, GIF, SVG, WebP - Max 2MB)</small>
                        <div class="mt-2">
                            <label for="logo_url" class="form-label">Or use Logo URL</label>
                            <input type="url" class="form-control" id="logo_url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://example.com/logo.png">
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Invoice Settings</h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', 'INV') }}" placeholder="INV">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="next_invoice_number" class="form-label">Next Invoice Number</label>
                            <input type="number" class="form-control" id="next_invoice_number" name="next_invoice_number" value="{{ old('next_invoice_number', 1) }}" min="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="accent_color" class="form-label">Invoice Accent Color</label>
                        <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ old('accent_color', '#31d8b2') }}" title="Choose accent color">
                        <small class="text-muted">Color used for headings, table headers, and totals</small>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Bank Information</h4>
                    
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank Name">
                    </div>

                    <div class="mb-3">
                        <label for="bank_account_name" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Account Holder Name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_routing_label" class="form-label">Routing Label</label>
                            <input type="text" class="form-control" id="bank_routing_label" name="bank_routing_label" value="{{ old('bank_routing_label', 'Routing Number') }}" placeholder="Routing Number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bank_routing_number" class="form-label">Routing Number</label>
                            <input type="text" class="form-control" id="bank_routing_number" name="bank_routing_number" value="{{ old('bank_routing_number') }}" placeholder="123456789">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_account_number" class="form-label">Account Number</label>
                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Account Number">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drives.invoice-profiles.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Profile
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

