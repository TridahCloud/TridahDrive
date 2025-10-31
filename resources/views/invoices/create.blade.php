@extends('layouts.dashboard')

@section('title', 'Create Invoice - ' . $drive->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Toolbar -->
    <div class="dashboard-card mb-3 p-3 d-flex justify-content-between align-items-center border-0">
        <div>
            <a href="{{ route('drives.invoices.index', $drive) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="customizeBtn">
                <i class="fas fa-cog me-1"></i>Customize
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="saveBtn">
                <i class="fas fa-save me-1"></i>Save
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>

    <!-- Customize Panel -->
    <div id="customizePanel" class="customize-panel">
        <div class="customize-panel-header">
            <h5 class="mb-0">
                <i class="fas fa-sliders-h me-2"></i>Customize Invoice
            </h5>
            <button type="button" class="btn btn-link text-reset p-0" id="closeCustomizeBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="customize-panel-body">
            <div class="customize-section">
                <h6 class="section-title">Company Information</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-company-name" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Company Name</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-company-address" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Company Address</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-company-phone" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Company Phone</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-company-email" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Company Email</span>
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Invoice Details</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-number" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Number</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-date" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Date</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-due-date" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Due Date</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-status" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Status</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-invoice-title" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Invoice Title</span>
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Customization</h6>
                <div class="customize-item">
                    <label for="invoice-color" class="form-label">Invoice Accent Color:</label>
                    <input type="color" class="form-control form-control-color" id="invoice-color" value="{{ $invoiceProfile?->accent_color ?? '#31d8b2' }}" title="Choose color">
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Client Information</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-name" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Name</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-address" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Address</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-client-email" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Client Email</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-project" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Project Name</span>
                </div>
            </div>

            <div class="customize-section">
                <h6 class="section-title">Other Sections</h6>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-items-table" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Items Table</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-payment-details" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Payment Details</span>
                </div>
                <div class="customize-item">
                    <label class="toggle-switch">
                        <input type="checkbox" id="show-totals" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Totals</span>
                </div>
            </div>
        </div>
    </div>
    <div id="customizePanelOverlay" class="customize-panel-overlay"></div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('drives.invoices.store', $drive) }}" method="POST" id="invoiceForm">
        @csrf
        
        <!-- Invoice Container - Looks like actual invoice -->
        <div class="invoice-container rounded shadow-sm p-5" style="max-width: 900px; margin: 0 auto; background-color: var(--bg-secondary, #f8f9fa);">
            <!-- Top Section: Logo & Invoice Info -->
            <div class="invoice-section d-flex justify-content-between align-items-start mb-5" id="company-info" data-section="company-info">
                <div class="flex-grow-1">
                    <h2 class="mb-2" data-field="company-name">
                        <span contenteditable="true" class="brand-teal">{{ $invoiceProfile?->company_name ?? 'Your Company' }}</span>
                    </h2>
                    <p class="text-muted mb-1" data-field="company-address">
                        <span contenteditable="true">{{ $invoiceProfile?->company_address ?? 'Your Address' }}</span>
                    </p>
                    <p class="text-muted mb-1" data-field="company-phone">
                        <span contenteditable="true">{{ $invoiceProfile?->company_phone ?? 'Your Phone' }}</span>
                    </p>
                    <p class="text-muted mb-0" data-field="company-email">
                        <span contenteditable="true">{{ $invoiceProfile?->company_email ?? 'your@email.com' }}</span>
                    </p>
                </div>
                <div class="text-end invoice-section" id="invoice-header" data-section="invoice-details" style="text-align: right;">
                    <h1 class="text-primary mb-3 invoice-heading" data-field="invoice-title" style="color: {{ $accentColor }}; text-align: right;">INVOICE</h1>
                    <div class="text-muted small" style="text-align: right;">
                        <div class="mb-1" data-field="invoice-number">
                            Invoice # <span class="invoice-number text-dark">{{ $invoiceProfile?->invoice_prefix ?? 'INV' }}-{{ str_pad($invoiceProfile?->next_invoice_number ?? 1, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="mb-1" data-field="invoice-date" style="text-align: right;">
                            Date: <input type="date" class="invoice-field" id="issue_date" name="issue_date" required style="text-align: right;">
                        </div>
                        <div class="mb-1" data-field="invoice-due-date" style="text-align: right;">
                            Due: <input type="date" class="invoice-field" id="due_date" name="due_date" style="text-align: right;">
                        </div>
                        <div data-field="invoice-status" style="text-align: right;">
                            Status: 
                            <select class="invoice-field" name="status" id="status" required style="text-align: right;">
                                <option value="draft" selected>Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill To Section -->
            <div class="invoice-section border-top pt-4 pb-4 mb-4" id="bill-to" data-section="bill-to">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
                            <div class="input-group mb-2" data-field="client-name">
                                <input type="text" class="form-control border-0 border-bottom" name="client_name" id="client_name" placeholder="Client Name" required>
                            </div>
                            <div class="input-group mb-2" data-field="client-address">
                                <input type="text" class="form-control border-0 border-bottom" name="client_address" id="client_address" placeholder="Client Address">
                            </div>
                            <div class="input-group mb-2" data-field="client-email">
                                <input type="email" class="form-control border-0 border-bottom" name="client_email" id="client_email" placeholder="Client Email">
                            </div>
                            <div class="input-group mb-2" data-field="project">
                                <input type="text" class="form-control border-0 border-bottom" name="project" id="project" placeholder="Project Name">
                            </div>
                        </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" id="importClientBtn" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Import Client
                        </button>
                        <ul class="dropdown-menu" id="clientDropdown">
                            @foreach($clients as $client)
                                <li>
                                    <a class="dropdown-item import-client" href="#" 
                                       data-name="{{ $client->name }}"
                                       data-email="{{ $client->email ?? '' }}"
                                       data-address="{{ $client->full_address ?? '' }}">
                                        {{ $client->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="invoice-section mb-4" id="items-table" data-section="items-table">
                <!-- Quick Add Items -->
                @if($userItems->count() > 0)
                <div class="mb-3">
                    <label class="form-label"><small class="text-muted">Quick Add Items:</small></label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($userItems as $item)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-add-item" 
                                data-name="{{ $item->name }}"
                                data-description="{{ $item->description ?? '' }}"
                                data-unit="{{ $item->unit ?? 'items' }}"
                                data-price="{{ $item->default_price ?? 0 }}">
                                <i class="fas fa-plus me-1"></i>{{ $item->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <table class="table table-bordered" id="itemsTable">
                    <thead class="bg-primary text-white" style="background-color: {{ $accentColor }};">
                        <tr>
                            <th style="width: 40%;">DESCRIPTION</th>
                            <th style="width: 12%;">QTY</th>
                            <th style="width: 18%;">UNIT</th>
                            <th style="width: 15%;">RATE</th>
                            <th style="width: 15%;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td>
                                <input type="text" class="form-control border-0 item-description" name="items[0][description]" placeholder="Enter description" required>
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 item-qty" name="items[0][quantity]" value="1" step="1" min="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control border-0" name="items[0][unit]" value="items">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 item-price" name="items[0][unit_price]" value="0" step="1" min="0" required>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="item-total">$0.00</span>
                                    <button type="button" class="btn btn-link btn-sm text-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                    <i class="fas fa-plus me-1"></i>Add Line Item
                </button>
            </div>

            <!-- Totals -->
            <div class="row">
                <div class="col-md-6 invoice-section" id="payment-details" data-section="payment-details">
                    <h6 class="text-uppercase text-muted mb-3">Payment Details:</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-2">
                                    <small class="text-muted">Bank:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_name ?? 'Your Bank' }}</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Account Name:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_account_name ?? 'Your Account' }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-2">
                                    <small class="text-muted">Routing Number:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_routing_number ?? '123456' }}</span>
                                </div>
                                <div>
                                    <small class="text-muted">Account Number:</small>
                                    <span contenteditable="true" class="d-block">{{ $invoiceProfile?->bank_account_number ?? '123456789' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 invoice-section" id="totals" data-section="totals">
                    <div class="text-end">
                        <div class="mb-3">
                            <strong>Subtotal: $<span id="subtotal" style="color: {{ $accentColor }};">0.00</span></strong>
                        </div>
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <label class="mb-0">Tax:</label>
                            <input type="number" class="form-control invoice-field-small" id="tax_rate" name="tax_rate" value="0" step="0.01" min="0" max="100" style="width: 80px;">
                            <span>% ($<span id="taxAmount" style="color: {{ $accentColor }};">0.00</span>)</span>
                        </div>
                        <div class="h3 mb-0">
                            <strong>Total: <span class="brand-teal">$<span id="total" style="color: {{ $accentColor }};">0.00</span></span></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.invoice-container [contenteditable="true"] {
    border-bottom: 1px dashed rgba(0, 0, 0, 0.2);
    min-height: 1.5em;
    outline: none;
    display: inline-block;
    padding: 0 2px;
}

.invoice-container [contenteditable="true"]:focus {
    border-bottom: 1px dashed #31d8b2;
    background-color: rgba(49, 216, 178, 0.05);
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCounter = 1;
    
    // Get initial accent color from profile
    const initialAccentColor = '{{ $accentColor }}';
    
    // Initialize accent color on page load
    function initializeAccentColor(color) {
        document.querySelectorAll('.invoice-heading').forEach(element => {
            element.style.color = color;
        });
        
        document.querySelectorAll('.brand-teal').forEach(element => {
            element.style.color = color;
        });
        
        document.getElementById('subtotal').style.color = color;
        document.getElementById('taxAmount').style.color = color;
        document.getElementById('total').style.color = color;
        
        document.querySelectorAll('.table thead, .table thead th').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Store in CSS variable
        document.documentElement.style.setProperty('--invoice-accent-color', color);
    }
    
    // Initialize with profile's accent color
    initializeAccentColor(initialAccentColor);
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 30);
    const dueDateStr = dueDate.toISOString().split('T')[0];
    
    if (document.getElementById('issue_date') && !document.getElementById('issue_date').value) {
        document.getElementById('issue_date').value = today;
    }
    if (document.getElementById('due_date') && !document.getElementById('due_date').value) {
        document.getElementById('due_date').value = dueDateStr;
    }
    
    // Add item button
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control border-0 item-description" name="items[${itemCounter}][description]" placeholder="Enter description" required>
            </td>
            <td>
                <input type="number" class="form-control border-0 item-qty" name="items[${itemCounter}][quantity]" value="1" step="1" min="0" required>
            </td>
            <td>
                <input type="text" class="form-control border-0" name="items[${itemCounter}][unit]" value="items">
            </td>
            <td>
                <input type="number" class="form-control border-0 item-price" name="items[${itemCounter}][unit_price]" value="0" step="1" min="0" required>
            </td>
            <td>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="item-total">$0.00</span>
                    <button type="button" class="btn btn-link btn-sm text-danger remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(newRow);
        attachItemListeners(newRow);
        itemCounter++;
    });
    
    // Remove item
    document.getElementById('itemsTable').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length > 1) {
                row.remove();
                calculateTotals();
            }
        }
    });
    
    // Quick Add Items
    document.querySelectorAll('.quick-add-item').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.dataset.name || '';
            const description = this.dataset.description || '';
            const unit = this.dataset.unit || 'items';
            const price = parseFloat(this.dataset.price) || 0;
            
            console.log('Adding item:', { name, unit, price });
            
            // Find the first empty or create a new row
            const tbody = document.getElementById('itemsBody');
            const firstRow = tbody.querySelector('.item-row');
            
            if (firstRow) {
                // Fill the first row
                const descriptionInput = firstRow.querySelector('.item-description');
                const unitInput = firstRow.querySelector('input[name*="[unit]"]');
                const priceInput = firstRow.querySelector('.item-price');
                
                console.log('Found inputs:', { descriptionInput, unitInput, priceInput });
                
                if (descriptionInput) {
                    if (!descriptionInput.value || descriptionInput.value.trim() === '') {
                        descriptionInput.value = name;
                    }
                }
                
                if (unitInput) {
                    unitInput.value = unit;
                    console.log('Set unit to:', unit);
                }
                
                if (priceInput) {
                    priceInput.value = price.toFixed(2);
                    console.log('Set price to:', price.toFixed(2));
                    
                    // Trigger input event to ensure calculations run
                    priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                
                // Calculate totals for this row
                calculateTotals();
            }
        });
    });
    
    // Client dropdown
    document.querySelectorAll('.import-client').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const clientName = this.dataset.name || '';
            const clientEmail = this.dataset.email || '';
            const clientAddress = this.dataset.address || '';
            
            console.log('Importing client:', { clientName, clientEmail, clientAddress });
            
            if (clientName) {
                const nameField = document.getElementById('client_name');
                if (nameField) {
                    nameField.value = clientName;
                    console.log('Set client name to:', clientName);
                }
            }
            
            if (clientEmail) {
                const emailField = document.getElementById('client_email');
                if (emailField) {
                    emailField.value = clientEmail;
                    console.log('Set client email to:', clientEmail);
                }
            }
            
            if (clientAddress) {
                const addressField = document.getElementById('client_address');
                if (addressField) {
                    addressField.value = clientAddress;
                    console.log('Set client address to:', clientAddress);
                }
            }
            
            // Close dropdown
            try {
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('importClientBtn'));
                if (dropdown) {
                    dropdown.hide();
                }
            } catch (err) {
                console.error('Error closing dropdown:', err);
            }
        });
    });
    
    // Calculate totals
    function calculateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = qty * price;
            
            row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            subtotal += total;
        });
        
        const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        const grandTotal = subtotal + taxAmount;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = taxAmount.toFixed(2);
        document.getElementById('total').textContent = grandTotal.toFixed(2);
    }
    
    // Attach listeners to item row
    function attachItemListeners(row) {
        row.querySelector('.item-qty').addEventListener('input', calculateTotals);
        row.querySelector('.item-price').addEventListener('input', calculateTotals);
    }
    
    // Attach to existing items
    document.querySelectorAll('.item-row').forEach(row => {
        attachItemListeners(row);
    });
    
    // Tax rate listener
    document.getElementById('tax_rate').addEventListener('input', calculateTotals);
    
    // Save button
    const saveBtn = document.getElementById('saveBtn');
    const invoiceForm = document.getElementById('invoiceForm');
    
    if (saveBtn && invoiceForm) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Save button clicked, submitting form...');
            
            // Re-index items array before submission
            const itemRows = document.querySelectorAll('.item-row');
            itemRows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input[name^="items["]');
                inputs.forEach(input => {
                    const name = input.name;
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.name = newName;
                });
            });
            
            invoiceForm.submit();
        });
    } else {
        console.error('Save button or invoice form not found!', { saveBtn, invoiceForm });
    }
    
    // Initial calculation
    calculateTotals();

    // Customize Panel Toggle
    document.getElementById('customizeBtn').addEventListener('click', function() {
        document.getElementById('customizePanel').classList.add('open');
        document.getElementById('customizePanelOverlay').classList.add('active');
    });

    document.getElementById('closeCustomizeBtn').addEventListener('click', function() {
        document.getElementById('customizePanel').classList.remove('open');
        document.getElementById('customizePanelOverlay').classList.remove('active');
    });

    document.getElementById('customizePanelOverlay').addEventListener('click', function() {
        document.getElementById('customizePanel').classList.remove('open');
        document.getElementById('customizePanelOverlay').classList.remove('active');
    });

    // Invoice color picker
    document.getElementById('invoice-color').addEventListener('change', function() {
        const color = this.value;
        
        // Update invoice heading
        document.querySelectorAll('.invoice-heading').forEach(element => {
            element.style.color = color;
        });
        
        // Update company name - target the brand-teal class on the contenteditable span
        document.querySelectorAll('.brand-teal').forEach(element => {
            element.style.color = color;
        });
        
        // Update total amount
        const totalElement = document.getElementById('total');
        if (totalElement) {
            totalElement.style.color = color;
        }
        
        // Update subtotal number
        const subtotalElement = document.getElementById('subtotal');
        if (subtotalElement) {
            subtotalElement.style.color = color;
        }
        
        // Update tax amount
        const taxAmountElement = document.getElementById('taxAmount');
        if (taxAmountElement) {
            taxAmountElement.style.color = color;
        }
        
        // Update table header background - apply to the thead element
        document.querySelectorAll('.table thead').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Also apply to th elements inside thead
        document.querySelectorAll('.table thead th').forEach(element => {
            element.style.backgroundColor = color;
        });
        
        // Store color for print (CSS variable)
        document.documentElement.style.setProperty('--invoice-accent-color', color);
    });

    // Field toggles
    const fieldMappings = {
        'show-company-name': 'company-name',
        'show-company-address': 'company-address',
        'show-company-phone': 'company-phone',
        'show-company-email': 'company-email',
        'show-invoice-title': 'invoice-title',
        'show-invoice-number': 'invoice-number',
        'show-invoice-date': 'invoice-date',
        'show-invoice-due-date': 'invoice-due-date',
        'show-invoice-status': 'invoice-status',
        'show-client-name': 'client-name',
        'show-client-address': 'client-address',
        'show-client-email': 'client-email',
        'show-project': 'project',
        'show-items-table': 'items-table',
        'show-payment-details': 'payment-details',
        'show-totals': 'totals'
    };

    document.querySelectorAll('#customizePanel input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const fieldKey = this.id;
            const fieldName = fieldMappings[fieldKey];
            
            if (fieldName) {
                const targetElements = document.querySelectorAll(`[data-field="${fieldName}"]`);
                targetElements.forEach(element => {
                    if (this.checked) {
                        element.style.display = '';
                    } else {
                        element.style.display = 'none';
                    }
                });
            }
        });
    });
});
</script>
@endpush
