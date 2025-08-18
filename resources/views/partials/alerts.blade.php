{{-- resources/views/partials/alerts.blade.php --}}

{{-- Success Messages --}}
@if(session('success') || session('status') || session('message'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" id="success-alert">
        <div class="d-flex align-items-start">
            <div class="me-3 mt-1">
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">
                    <i class="bi bi-check2"></i> Success!
                </h6>
                <p class="mb-0">
                    {{ session('success') ?? session('status') ?? session('message') }}
                </p>
                @if(session('success_details'))
                    <small class="text-muted d-block mt-1">
                        {{ session('success_details') }}
                    </small>
                @endif
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Error Messages --}}
@if(session('error') || session('danger'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" id="error-alert">
        <div class="d-flex align-items-start">
            <div class="me-3 mt-1">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">
                    <i class="bi bi-x-circle"></i> Error!
                </h6>
                <p class="mb-0">
                    {{ session('error') ?? session('danger') }}
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Warning Messages --}}
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" id="warning-alert">
        <div class="d-flex align-items-start">
            <div class="me-3 mt-1">
                <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">
                    <i class="bi bi-exclamation-triangle"></i> Warning!
                </h6>
                <p class="mb-0">
                    {{ session('warning') }}
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Info Messages --}}
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" id="info-alert">
        <div class="d-flex align-items-start">
            <div class="me-3 mt-1">
                <i class="bi bi-info-circle-fill text-info fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">
                    <i class="bi bi-info-circle"></i> Information
                </h6>
                <p class="mb-0">
                    {{ session('info') }}
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" id="validation-alert">
        <div class="d-flex align-items-start">
            <div class="me-3 mt-1">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <i class="bi bi-x-circle"></i> Validation Errors
                </h6>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Custom Action Success --}}
@if(session('action_success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" id="action-success-alert">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                    @if(session('action_icon'))
                        <i class="{{ session('action_icon') }} text-success fs-4"></i>
                    @else
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    @endif
                </div>
            </div>
            <div class="col">
                <h6 class="mb-1 text-success">
                    {{ session('action_title', 'Action Completed') }}
                </h6>
                <p class="mb-0 text-dark">
                    {{ session('action_success') }}
                </p>
                @if(session('action_link'))
                    <a href="{{ session('action_link') }}" class="btn btn-sm btn-outline-success mt-2">
                        {{ session('action_link_text', 'View Details') }}
                    </a>
                @endif
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Auto-dismiss Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss success alerts after 5 seconds
        const alertIds = ['success-alert', 'action-success-alert', 'info-alert'];
        
        alertIds.forEach(function(alertId) {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                setTimeout(function() {
                    const alert = new bootstrap.Alert(alertElement);
                    alert.close();
                }, 5000);
            }
        });

        // Keep error and warning alerts visible longer (8 seconds)
        const errorAlertIds = ['error-alert', 'warning-alert', 'validation-alert'];
        
        errorAlertIds.forEach(function(alertId) {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                setTimeout(function() {
                    const alert = new bootstrap.Alert(alertElement);
                    alert.close();
                }, 8000);
            }
        });
    });
</script>

{{-- Custom Styles --}}
<style>
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left: 4px solid #28a745;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
        border-left: 4px solid #dc3545;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #fde8a6 100%);
        border-left: 4px solid #ffc107;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #b8daff 100%);
        border-left: 4px solid #17a2b8;
    }
</style>