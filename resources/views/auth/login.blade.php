@extends('layouts.auth')

@section('content')
<div class="min-vh-100 d-flex align-items-center position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    
    <!-- Subtle Background Pattern -->
    <div class="position-absolute w-100 h-100 opacity-10" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></g></svg>');"></div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                
                <!-- Modern Login Card -->
                <div class="card border-0 shadow-lg" style="border-radius: 20px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body p-5">
                        
                        <!-- Header with Icon -->
                        <div class="text-center mb-5">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm" 
                                     style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-rocket text-white" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <h1 class="h3 fw-bold mb-2" style="color: #2d3748;">Welcome Back</h1>
                            <p class="text-muted mb-0">Sign in to continue your journey</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Field -->
                            <!-- Demo Role Selector -->
                            <div class="mb-4">
                                <label for="demo-role" class="form-label fw-semibold text-dark">Select Role (Demo)</label>
                                <select id="demo-role" class="form-select form-select-lg bg-light border-0" style="border-radius: 12px;">
                                    <option value="">-- Choose Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="applicant">Applicant</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email"
                                        class="form-control form-control-lg border-0 bg-light @error('email') is-invalid @enderror" 
                                        value="{{ old('email') }}" 
                                        placeholder="Enter your email"
                                        required 
                                        autofocus
                                        style="border-radius: 0 12px 12px 0 !important;"
                                    />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-dark">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password"
                                        class="form-control form-control-lg border-0 bg-light @error('password') is-invalid @enderror" 
                                        placeholder="Enter your password"
                                        required
                                        style="border-radius: 0 !important;"
                                    />
                                    <button type="button" class="btn btn-outline-secondary border-0 bg-light" onclick="togglePassword()" style="border-radius: 0 12px 12px 0 !important;">
                                        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="remember" 
                                        id="remember"
                                        style="border-radius: 4px;"
                                        {{ old('remember') ? 'checked' : '' }}
                                    />
                                    <label class="form-check-label text-muted" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none" style="color: #667eea; font-weight: 500;">
                                    Forgot Password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-lg py-3 fw-semibold text-white border-0 shadow-sm" 
                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; transition: all 0.3s ease;">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Sign In to Account
                                </button>
                            </div>

                            <!-- Divider -->
                            <div class="text-center mb-4">
                                <div class="d-flex align-items-center">
                                    <hr class="flex-grow-1">
                                    <span class="px-3 text-muted small">Or continue with</span>
                                    <hr class="flex-grow-1">
                                </div>
                            </div>

                            <!-- Social Login Buttons -->
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center border-2" 
                                            style="border-radius: 12px; transition: all 0.3s ease;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" class="me-2">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <span class="fw-medium">Google</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('auth.linkedin') }}" class="btn w-100 py-2 d-flex align-items-center justify-content-center text-decoration-none text-white border-0" 
                                       style="background: #0077b5; border-radius: 12px; transition: all 0.3s ease;">
                                        <i class="fab fa-linkedin me-2"></i>
                                        <span class="fw-medium">LinkedIn</span>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Registration Link -->
                <div class="text-center mt-4">
                    <p class="text-white mb-0">
                        New to our platform? 
                        <a href="{{ route('register') }}" class="text-white text-decoration-none fw-bold">
                            Create your account →
                        </a>
                    </p>
                </div>

                <!-- Footer Links -->
                <div class="text-center mt-3">
                    <div class="d-flex justify-content-center gap-4">
                        <a href="#" class="text-white text-decoration-none small opacity-75">Privacy</a>
                        <a href="#" class="text-white text-decoration-none small opacity-75">Terms</a>
                        <a href="#" class="text-white text-decoration-none small opacity-75">Support</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Smooth hover effects */
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #667eea;
    color: #667eea;
}

a[style*="#0077b5"]:hover {
    background: #005885 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 119, 181, 0.3);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
    border-radius: 12px 0 0 12px !important;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .card-body {
        padding: 2rem !important;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
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

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
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

    // Demo role autofill logic
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('demo-role');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        const credentials = {
            admin: {
                email: 'hello@tokelofoso.online',
                password: 'Tokelo@23'
            },
            manager: {
                email: 'tokelo.foso@cbs.co.ls',
                password: 'Tokelo@23'
            },
            applicant: {
                email: 'tokelo.foso23@gmail.com',
                password: 'Tokelo@23'
            }
        };

        roleSelect.addEventListener('change', function () {
            const selected = roleSelect.value;
            if (credentials[selected]) {
                emailInput.value = credentials[selected].email;
                passwordInput.value = credentials[selected].password;
            } else {
                emailInput.value = '';
                passwordInput.value = '';
            }
        });
    });
</script>

@endsection