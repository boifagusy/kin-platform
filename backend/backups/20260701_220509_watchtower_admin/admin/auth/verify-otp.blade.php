<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Verify OTP</h1>

            <p class="text-gray-600 mb-4 text-center">Enter the 6-digit OTP sent to your email.</p>

            <form method="POST" action="{{ route('admin.password.verify') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">OTP Code</label>
                    <input type="text" name="otp" maxlength="6" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 text-center text-2xl tracking-widest"
                           placeholder="000000">
                    @error('otp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    Verify OTP
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('admin.password.request') }}" class="text-sm text-gray-600 hover:text-gray-800">Request New OTP</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
