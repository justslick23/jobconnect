@extends('layouts.auth')

@section('content')
<div class="min-vh-100 d-flex align-items-center auth-background">
    <!-- Background Image Overlay -->
    <div class="background-overlay"></div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                
                <!-- Registration Card -->
                <div class="auth-card">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h1 class="auth-title">Join Us</h1>
                        <p class="auth-subtitle">Create your account and start your career journey</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Full Name -->
                        <div class="field-group">
                            <div class="field-label">
                                <i class="fas fa-user"></i>
                                <span>Full Name</span>
                            </div>
                            <input 
                                id="name" 
                                type="text" 
                                name="name"
                                class="field-input @error('name') error @enderror" 
                                value="{{ old('name') }}" 
                                placeholder="Your full name"
                                required
                            />
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="field-group">
                            <div class="field-label">
                                <i class="fas fa-at"></i>
                                <span>Email</span>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                name="email"
                                class="field-input @error('email') error @enderror" 
                                value="{{ old('email') }}" 
                                placeholder="your@email.com"
                                required
                            />
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Fields Row -->
                        <div class="password-row">
                            <div class="field-group">
                                <div class="field-label">
                                    <i class="fas fa-key"></i>
                                    <span>Password</span>
                                </div>
                                <div class="password-wrapper">
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password"
                                        class="field-input @error('password') error @enderror" 
                                        placeholder="Create password"
                                        required
                                    />
                                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                        <i class="fas fa-eye" id="toggleIcon1"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="field-group">
                                <div class="field-label">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Confirm</span>
                                </div>
                                <div class="password-wrapper">
                                    <input 
                                        id="password_confirmation" 
                                        type="password" 
                                        name="password_confirmation"
                                        class="field-input" 
                                        placeholder="Confirm password"
                                        required
                                    />
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                                        <i class="fas fa-eye" id="toggleIcon2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    

                        <!-- Create Account Button -->
                        <button type="submit" class="signin-btn">
                            <span>Create Account</span>
                            <i class="fas fa-user-plus"></i>
                        </button>

                        <!-- Divider -->
                        <div class="or-divider">
                            <span>or</span>
                        </div>

                      
                    </form>

                    <!-- Login Link -->
                    <div class="bottom-link">
                        Already have an account? <a href="{{ route('login') }}">Sign in</a>
                    </div>
                </div>

                <!-- Features -->
             
            </div>
        </div>
    </div>
</div>

<style>
/* Background with image overlay */
.auth-background {
    background: 
        linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 50%, rgba(79, 172, 254, 0.8) 100%),
        url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;
    position: relative;
}

.background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg, 
        rgba(102, 126, 234, 0.15) 0%, 
        rgba(118, 75, 162, 0.25) 50%, 
        rgba(79, 172, 254, 0.15) 100%
    );
    backdrop-filter: blur(1px);
}

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
    margin-bottom: 1.5rem;
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

/* Password fields row */
.password-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

/* Password wrapper */
.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.2s;
}

.password-toggle:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

/* Auth options */
.auth-options {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
}

.remember-check {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    user-select: none;
    line-height: 1.4;
}

.remember-check input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #e2e8f0;
    border-radius: 4px;
    margin-right: 0.5rem;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.remember-check input:checked + .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.remember-check input:checked + .checkmark::after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.remember-text {
    font-size: 0.875rem;
    color: #4a5568;
}

.terms-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.terms-link:hover {
    color: #5a67d8;
}

/* Create Account button */
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

/* OR divider */
.or-divider {
    text-align: center;
    position: relative;
    margin: 1.5rem 0;
    color: #a0aec0;
    font-size: 0.875rem;
    font-weight: 500;
}

.or-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
}

.or-divider span {
    background: rgba(255, 255, 255, 0.98);
    padding: 0 1rem;
}

/* Social buttons */
.social-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.875rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    text-decoration: none;
    font-size: 1.25rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.social-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.social-btn.google {
    color: #ea4335;
    border-color: #ea4335;
}

.social-btn.google:hover {
    background: #ea4335;
    color: white;
}

.social-btn.linkedin {
    color: #0077b5;
    border-color: #0077b5;
}

.social-btn.linkedin:hover {
    background: #0077b5;
    color: white;
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

/* Features grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 1rem;
}

.feature-item {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 1rem 0.75rem;
    text-align: center;
    backdrop-filter: blur(10px);
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.feature-item i {
    display: block;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}

.feature-item span {
    font-size: 0.8rem;
    font-weight: 600;
    color: #2d3748;
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .password-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .auth-card {
        padding: 1.5rem;
        margin: 1rem;
    }
    
    .social-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .feature-item {
        padding: 0.75rem;
    }
}

@media (max-width: 576px) {
    .features-grid {
        display: none; /* Hide features on very small screens to save space */
    }
    
    .password-row .field-label span {
        font-size: 0.8rem; /* Smaller labels on mobile */
    }
}
</style>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

@endsection