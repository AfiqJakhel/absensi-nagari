<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Nagari</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome for icons (temporary CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="antialiased bg-gray-50 text-gray-900 font-sans">
    <div class="min-h-screen flex items-center justify-center relative bg-cover bg-center" style="background-image: url('{{ asset('images/bg-login.png') }}');">
        
        <!-- Dark overlay to make the card stand out -->
        <div class="absolute inset-0 bg-black/40 z-0"></div>

        <!-- Centered Card -->
        <div class="w-full max-w-lg bg-gradient-to-br from-green-50 via-teal-300 to-teal-600 opacity-95 backdrop-blur-xl rounded-2xl sm:rounded-[2rem] shadow-2xl p-5 sm:p-10 md:p-12 relative z-10 mx-4 sm:mx-auto my-6 sm:my-0">
            
            <div class="w-full mx-auto">
                <div class="text-center mb-6 sm:mb-8 md:mb-10">
                    <div class="flex items-center justify-center gap-3 sm:gap-4 md:gap-6 mb-4 sm:mb-6 md:mb-8">
                        <img src="{{ asset('images/logo-pesisir.png') }}" alt="Logo Pesisir Selatan" class="h-10 sm:h-16 md:h-20 w-auto object-contain mix-blend-multiply">
                        <img src="{{ asset('images/logo-pantai.svg') }}" alt="Logo Nagari" class="h-10 sm:h-16 md:h-20 w-auto object-contain mix-blend-multiply">
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Sistem Absensi</h1>
                    <p class="text-gray-700 text-sm sm:text-base md:text-lg font-medium">Kantor Wali Nagari</p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-4 sm:space-y-6">
                    @csrf
                    <!-- Username -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-500 sm:text-gray-400 text-sm sm:text-base"></i>
                            </div>
                            <input type="text" name="username" id="username" value="{{ old('username') }}"
                                class="pl-9 sm:pl-11 pr-3 sm:pr-4 py-2.5 sm:py-3.5 block w-full border-gray-300 rounded-lg sm:rounded-xl bg-gray-50 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm sm:text-base" 
                                placeholder="Username" required>
                        </div>
                        @error('username')
                            <p class="text-red-500 text-xs mt-1.5 font-medium ml-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-500 sm:text-gray-400 text-sm sm:text-base"></i>
                            </div>
                            <input type="password" name="password" id="password" 
                                class="pl-9 sm:pl-11 pr-10 sm:pr-12 py-2.5 sm:py-3.5 block w-full border-gray-300 rounded-lg sm:rounded-xl bg-gray-50 border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm sm:text-base" 
                                placeholder="Password" required>
                            <div class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center cursor-pointer">
                                <i class="fa-regular fa-eye-slash text-gray-500 sm:text-gray-400 hover:text-gray-700 transition-colors text-sm sm:text-base"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mt-4 sm:mt-6">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" 
                                class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <label for="remember_me" class="ml-2 block text-xs sm:text-sm text-gray-700 sm:text-gray-600 cursor-pointer">
                                Ingat Saya
                            </label>
                        </div>

                        <div class="text-xs sm:text-sm">
                            <a href="#" class="font-medium text-blue-700 sm:text-blue-600 hover:text-blue-800 sm:hover:text-blue-500 transition-colors">
                                Lupa Password?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-1 sm:pt-2">
                        <button type="submit" 
                            class="w-full flex justify-center items-center py-2.5 sm:py-3.5 px-4 border border-transparent rounded-lg sm:rounded-xl shadow-md text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            Masuk  <i class="fa-solid fa-arrow-right-to-bracket ml-2 text-xs sm:text-sm"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
