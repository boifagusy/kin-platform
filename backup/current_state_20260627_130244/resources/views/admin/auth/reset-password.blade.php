<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Set New Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --kin-primary: #1A5632;
            --kin-primary-light: #2D7A48;
            --kin-secondary: #D4A017;
        }
        
        body {
            background: linear-gradient(135deg, #F0F7F2 0%, #E8F3EC 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .reset-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(26,86,50,0.1);
            border: 1px solid rgba(212,160,23,0.2);
        }
        
        .password-strength {
            height: 4px;
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        .strength-weak { background: #dc2626; width: 25%; }
        .strength-medium { background: #f59e0b; width: 50%; }
        .strength-strong { background: #10b981; width: 75%; }
        .strength-very-strong { background: #059669; width: 100%; }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--kin-primary) 0%, var(--kin-primary-light) 100%);
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 40px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,86,50,0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="reset-card max-w-md w-full p-8">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🐵</div>
            <h1 class="text-2xl font-bold text-primary mb-2">Set New Password</h1>
            <p class="text-gray-600 text-sm">Create a strong password for your account</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" id="resetForm">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="8"
                           onkeyup="checkPasswordStrength()"
                           class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:border-primary">
                    <button type="button" 
                            onclick="togglePassword('password', this)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xl bg-transparent border-0 cursor-pointer">
                        🙈
                    </button>
                </div>
                <div class="password-strength mt-2 rounded-full" id="strengthBar"></div>
                <div class="text-xs text-gray-500 mt-1" id="strengthText"></div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <div class="relative">
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:border-primary">
                    <button type="button" 
                            onclick="togglePassword('password_confirmation', this)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xl bg-transparent border-0 cursor-pointer">
                        🙈
                    </button>
                </div>
            </div>

            <button type="submit" id="submitBtn" class="btn-primary w-full">
                Reset Password
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('admin.login') }}" class="text-sm text-gray-500 hover:text-primary transition">
                    ← Back to Login
                </a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🐵';
            } else {
                input.type = 'password';
                button.textContent = '🙈';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength mt-2 rounded-full';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength === 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#dc2626';
            } else if (strength === 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#f59e0b';
            } else if (strength === 3) {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#10b981';
            } else {
                strengthBar.classList.add('strength-very-strong');
                strengthText.textContent = 'Very strong password!';
                strengthText.style.color = '#059669';
            }
        }
    </script>
</body>
</html>
