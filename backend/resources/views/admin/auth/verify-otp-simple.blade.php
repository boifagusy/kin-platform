<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verify OTP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* KIN Brand Colors */
        :root {
            --kin-primary: #1A5632;
            --kin-primary-light: #2D7A48;
            --kin-secondary: #D4A017;
            --kin-danger: #DC3545;
            --kin-gray-100: #F8F9FA;
            --kin-gray-200: #E9ECEF;
            --kin-gray-700: #495057;
            --kin-gray-800: #212529;
        }
        
        body {
            background: linear-gradient(135deg, #F0F7F2 0%, #E8F3EC 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .otp-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(26,86,50,0.1);
            border: 1px solid rgba(212,160,23,0.2);
        }
        
        .otp-title {
            color: var(--kin-primary);
            font-weight: 700;
        }
        
        .otp-subtitle {
            color: var(--kin-gray-700);
        }
        
        .otp-input {
            width: 52px;
            height: 64px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            color: var(--kin-primary);
            background: white;
            border: 2px solid var(--kin-gray-200);
            border-radius: 16px;
            margin: 0 6px;
            transition: all 0.2s ease;
        }
        
        .otp-input:focus {
            border-color: var(--kin-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26,86,50,0.1);
            transform: scale(1.02);
        }
        
        /* Remove number spinner arrows */
        .otp-input::-webkit-outer-spin-button,
        .otp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .otp-input[type=number] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        
        .countdown {
            font-size: 16px;
            font-weight: 600;
            color: var(--kin-secondary);
            background: #FEF8E7;
            padding: 6px 12px;
            border-radius: 30px;
            display: inline-block;
        }
        
        .countdown-expired {
            color: var(--kin-primary);
            background: #E8F5E9;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--kin-primary) 0%, var(--kin-primary-light) 100%);
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 40px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,86,50,0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-resend {
            color: var(--kin-secondary);
            background: transparent;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-resend:hover {
            color: #B8860B;
            text-decoration: underline;
        }
        
        .error-message {
            background: #FEE2E2;
            color: var(--kin-danger);
            border-radius: 12px;
            padding: 10px;
            font-size: 13px;
            border-left: 4px solid var(--kin-danger);
        }
        
        .email-display {
            background: #F0F7F2;
            padding: 8px 16px;
            border-radius: 40px;
            display: inline-block;
            font-size: 13px;
            color: var(--kin-primary);
            font-weight: 500;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="otp-card max-w-md w-full p-8">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🔐</div>
            <h1 class="otp-title text-2xl mb-2">Verify OTP</h1>
            <p class="otp-subtitle text-sm">Enter the 6-digit code sent to</p>
            <div class="email-display mt-2">{{ $email }}</div>
        </div>

        @if(session('error'))
            <div class="error-message mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.verify') }}" id="otpForm">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- OTP Input Group with numeric keyboard -->
            <div class="flex justify-center flex-wrap gap-2 mb-6">
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp1" name="otp1" maxlength="1" autofocus>
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp2" name="otp2" maxlength="1">
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp3" name="otp3" maxlength="1">
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp4" name="otp4" maxlength="1">
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp5" name="otp5" maxlength="1">
                <input type="number" inputmode="numeric" pattern="[0-9]*" 
                       class="otp-input" id="otp6" name="otp6" maxlength="1">
            </div>
            <input type="hidden" name="otp" id="otpHidden">

            <!-- Countdown Timer -->
            <div class="text-center mb-4">
                <span class="text-sm text-gray-500">⏱️ Code expires in </span>
                <span id="countdown" class="countdown">60</span>
                <span class="text-sm text-gray-500"> seconds</span>
            </div>

            <!-- Resend Button -->
            <div class="text-center mb-4">
                <button type="button" id="resendBtn" class="btn-resend text-sm hidden">
                    🔄 Resend OTP
                </button>
            </div>

            @error('otp')
                <p class="text-red-500 text-xs text-center mb-4">{{ $message }}</p>
            @enderror

            <!-- Submit Button -->
            <button type="submit" id="submitBtn" class="btn-primary w-full">
                Verify OTP
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('admin.password.request') }}" class="text-sm text-gray-500 hover:text-primary transition">
                    ← Back to Forgot Password
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-tab OTP inputs
        const otpInputs = ['otp1', 'otp2', 'otp3', 'otp4', 'otp5', 'otp6'];
        
        // Force numeric input only
        otpInputs.forEach((id, index) => {
            const input = document.getElementById(id);
            
            // Restrict to numbers only
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 1 && index < 5) {
                    document.getElementById(otpInputs[index + 1]).focus();
                }
                // Auto-submit when all 6 digits are entered
                const allFilled = otpInputs.every(inputId => document.getElementById(inputId).value.length === 1);
                if (allFilled) {
                    submitOtp();
                }
            });
            
            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    document.getElementById(otpInputs[index - 1]).focus();
                }
            });
            
            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = paste.replace(/[^0-9]/g, '').split('');
                for (let i = 0; i < Math.min(numbers.length, otpInputs.length); i++) {
                    document.getElementById(otpInputs[i]).value = numbers[i];
                }
                // Check if all filled after paste
                const allFilled = otpInputs.every(inputId => document.getElementById(inputId).value.length === 1);
                if (allFilled) {
                    submitOtp();
                } else {
                    // Focus on first empty field
                    for (let i = 0; i < otpInputs.length; i++) {
                        if (!document.getElementById(otpInputs[i]).value) {
                            document.getElementById(otpInputs[i]).focus();
                            break;
                        }
                    }
                }
            });
        });

        function getOtp() {
            return otpInputs.map(id => document.getElementById(id).value).join('');
        }

        function submitOtp() {
            const otp = getOtp();
            if (otp.length === 6) {
                document.getElementById('otpHidden').value = otp;
                document.getElementById('otpForm').submit();
            }
        }

        // Countdown timer
        let countdown = 60;
        let countdownInterval;
        const countdownEl = document.getElementById('countdown');
        const submitBtn = document.getElementById('submitBtn');
        const resendBtn = document.getElementById('resendBtn');
        const form = document.getElementById('otpForm');

        function startCountdown() {
            submitBtn.disabled = true;
            countdownInterval = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    countdownEl.textContent = '0';
                    countdownEl.classList.add('countdown-expired');
                    submitBtn.disabled = false;
                    resendBtn.classList.remove('hidden');
                }
            }, 1000);
        }

        // Resend OTP
        resendBtn.addEventListener('click', async () => {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('/admin/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: '{{ $email }}' })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Reset OTP inputs
                    otpInputs.forEach(id => document.getElementById(id).value = '');
                    document.getElementById('otp1').focus();
                    
                    // Reset countdown
                    countdown = 60;
                    countdownEl.classList.remove('countdown-expired');
                    startCountdown();
                    resendBtn.classList.add('hidden');
                    alert('New OTP sent to your email');
                } else {
                    alert(data.message || 'Failed to resend OTP');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                resendBtn.disabled = false;
                resendBtn.textContent = '🔄 Resend OTP';
            }
        });

        startCountdown();

        form.addEventListener('submit', function(e) {
            if (countdown > 0) {
                e.preventDefault();
                alert('Please wait ' + countdown + ' seconds before verifying OTP');
                return false;
            }
        });
    </script>
</body>
</html>
