<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Project Invitation - {{ $invitation->project->name }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- App Logo -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-slate-900 dark:text-white tracking-tight">Collab<span class="text-indigo-600">Task</span></span>
                </div>
            </div>

            <!-- Invitation Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 shadow-xl space-y-6">
                <!-- Project Presentation -->
                <div class="text-center space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $invitation->project->color ?? '#4F46E5' }};"></span>
                        <span>Project Invitation</span>
                    </div>

                    <h1 class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ $invitation->project->name }}
                    </h1>

                    <p class="text-xs text-slate-500">
                        <strong>{{ $invitation->inviter?->name ?? 'A team member' }}</strong> has invited you to collaborate as a
                        <span class="font-bold uppercase text-indigo-600">{{ $invitation->role->label() }}</span>.
                    </p>
                </div>

                @if($invitation->project->description)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-slate-800">
                        {{ $invitation->project->description }}
                    </div>
                @endif

                <!-- PATH A: User is Logged In -->
                @if(Auth::check())
                    <div class="space-y-4 pt-2">
                        <div class="p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex items-center gap-3">
                            <x-avatar :user="Auth::user()" size="md" />
                            <div class="text-xs truncate">
                                <div class="font-bold text-slate-900 dark:text-white">Signed in as {{ Auth::user()->name }}</div>
                                <div class="text-slate-500 truncate">{{ Auth::user()->email }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('invitations.accept', ['token' => $token]) }}">
                            @csrf
                            <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">
                                Accept Invitation & Join Workspace
                            </button>
                        </form>
                    </div>

                <!-- PATH B: User is Guest (Needs Registration) -->
                @else
                    <form method="POST" action="{{ route('invitations.register', ['token' => $token]) }}" class="space-y-4 pt-2">
                        @csrf

                        <!-- Locked Email -->
                        <div>
                            <x-input-label for="email" :value="__('Invitation Email')" />
                            <x-text-input id="email" type="email" class="mt-1 block w-full bg-slate-100 dark:bg-slate-800 text-slate-500 cursor-not-allowed" :value="$invitation->email" readonly />
                        </div>

                        <!-- Full Name -->
                        <div>
                            <x-input-label for="name" :value="__('Your Full Name *')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="John Doe" />
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Create Password *')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" placeholder="At least 8 characters" />
                            <x-input-error class="mt-1" :messages="$errors->get('password')" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password *')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                        </div>

                        <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">
                            Create Account & Join Project
                        </button>
                    </form>

                    <div class="text-center pt-2 text-xs text-slate-500">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Log in</a>
                    </div>
                @endif
            </div>

            <div class="text-center mt-6 text-xs text-slate-400">
                This invitation link is cryptographically protected and expires in {{ $invitation->expires_at->diffForHumans() }}.
            </div>
        </div>
    </body>
</html>

