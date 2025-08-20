@extends('layouts.auth')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%);">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                
                <!-- Password Reset Request Card -->
                <div class="auth-card">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h1 class="auth-title">Reset Password</h1>
                        <p class="auth-subtitle">Enter your email to receive reset instructions</p>
                    </div>

                    <!-- Success Message -->
                    @if (session('status'))
                        <div class="alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email -->
                        <div class="field-group">
                            <div class="field-label">
                                <i class="fas fa-at"></i>
                                <span>Email Address</span>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                name="email"
                                class="field-input @error('email') error @enderror" 
                                value="{{ old('email') }}" 
                                placeholder="your@email.com"
                                required
                                autocomplete="email"
                                autofocus
                            />
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Send Reset Link Button -->
                        <button type="submit" class="signin-btn">
                            <span>Send Reset Link</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <!-- Back to Login -->
                    <div class="bottom-link">
                        Remember your password? <a href="{{ route('login') }}">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Main container */
.auth-card {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 
        0 30px 60px rgba(0, 0, 0, 0.12),
        0 0 0 1px rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Header */
.auth-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
}

.auth-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.auth-subtitle {
    color: #718096;
    font-size: 0.95rem;
    margin: 0;
}

/* Success Alert */
.alert-success {
    background: linear-gradient(135deg, #48bb78, #38a169);
    color: white;
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

/* Field groups */
.field-group {
    margin-bottom: 1.25rem;
}

.field-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2d3748;
    font-size: 0.875rem;
}

.field-label i {
    color: #667eea;
    width: 16px;
}

.field-input {
    width: 100%;
    background: #f7fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 0.95rem;
    color: #2d3748;
    transition: all 0.2s ease;
}

.field-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.field-input::placeholder {
    color: #a0aec0;
}

.field-input.error {
    border-color: #f56565;
    background: #fed7d7;
}

.field-error {
    color: #e53e3e;
    font-size: 0.825rem;
    margin-top: 0.25rem;
    font-weight: 500;
}

/* Sign in button */
.signin-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.signin-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.signin-btn:active {
    transform: translateY(0);
}

/* Bottom link */
.bottom-link {
    text-align: center;
    color: #718096;
    font-size: 0.9rem;
}

.bottom-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.bottom-link a:hover {
    color: #5a67d8;
}

/* Responsive */
@media (max-width: 576px) {
    .auth-card {
        padding: 1.5rem;
        margin: 1rem;
    }
}
</style>

@endsection