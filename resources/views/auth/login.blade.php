@extends('layouts.guest', ['title'=> 'Connexion'])
@section('content')
<div class="relative flex min-h-screen">
    <div class="relative flex flex-col flex-shrink-0 w-full min-h-full bg-white dark:bg-gray-950">
        <div class="m-auto w-full max-w-[420px] xs:max-w-[520px] p-5">
            <div class="relative flex justify-center flex-shrink-0 pb-6">
                <a href="{{ url('/') }}" class="relative inline-block h-10 transition-opacity duration-300">
                    <img class="h-full opacity-0 dark:opacity-100" src="{{ asset('images/logo-dark.png') }}" srcset="{{ asset('images/logo-dark.png') }}" alt="logo">
                    <img class="absolute top-0 h-full opacity-100 dark:opacity-0 start-0" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo.png') }}" alt="logo">
                </a>
            </div>
            <div class="p-5 border border-gray-300 rounded dark:border-gray-900 sm:p-6 md:p-10">
                <div class="pb-5">
                    <h5 class="mb-2 text-xl font-bold font-heading -tracking-snug text-slate-700 dark:text-white leading-tighter">Connexion</h5>
                    <p class="text-sm leading-6 text-slate-400">
                        Accédez au panneau SmartScol en utilisant votre identifiant et votre mot de passe.
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="relative mb-5 last:mb-0">
                        <x-input-label for="username" :value="__('Identifiant')" class="flex items-center justify-between mb-2 text-sm font-medium text-slate-700 dark:text-white" />
                        <div class="relative">
                            <x-text-input id="username"
                                class="block w-full box-border text-sm leading-4.5 px-4 py-2.5 h-11 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all"
                                type="text"
                                name="username"
                                :value="old('username')"
                                placeholder="Entrez votre identifiant"
                                required
                                autofocus
                                autocomplete="username" />
                        </div>
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>
                    <div class="relative mb-5 last:mb-0">
                        <div class="flex items-center justify-between mb-2">
                            <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700 dark:text-white" />
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="inline-flex text-xs font-medium leading-none transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600">
                                    {{ __('Mot de passe oublié?') }}
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <a tabindex="-1" href="#password" class="absolute top-0 flex items-center justify-center h-11 w-11 end-0 js-password-toggle group/password">
                                <em class="group-[.is-shown]/password:hidden text-slate-400 text-base leading-none ni ni-eye"></em>
                                <em class="hidden group-[.is-shown]/password:block text-slate-400 text-base leading-none ni ni-eye-off"></em>
                            </a>
                            <x-text-input id="password"
                                class="block w-full box-border text-sm leading-4.5 px-4 py-2.5 h-11 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all"
                                type="password"
                                name="password"
                                placeholder="Entrez votre mot de passe"
                                required
                                autocomplete="current-password" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <!-- Remember Me -->
                    <div class="relative mb-5 last:mb-0">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="border-gray-300 rounded shadow-sm text-primary-600 dark:bg-gray-900 dark:border-gray-700 focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-gray-800" name="remember">
                            <span class="text-sm text-slate-600 ms-2 dark:text-slate-400">{{ __('Remember me') }}</span>
                        </label>
                    </div>
                    <div class="relative mb-5 last:mb-0">
                        <x-primary-button class="relative w-full flex items-center justify-center text-center align-middle text-base font-bold leading-4.5 rounded-md px-6 py-3 tracking-wide border border-primary-600 text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 transition-all duration-300">
                            {{ __('Se connecter') }}
                        </x-primary-button>
                    </div>
                </form>

                @if (Route::has('register'))
                <div class="pt-6">
                    Nouveau sur notre plateforme?
                    <a class="transition-all duration-300 text-primary-500 hover:text-primary-600" href="{{ route('register') }}">Créer un compte</a>
                </div>
                @endif
            </div>
        </div>
        <!-- Footer section centrée -->
        <div class="py-6 border-t border-gray-200 dark:border-gray-800 px-5.5">
            <div class="container max-w-7xl">
                <div class="flex flex-wrap justify-center -m-2">
                    <div class="w-full p-2">
                        <p class="text-center text-slate-400 text-sm/4">&copy;
                            {{ date('Y') }} {{config('app.name')}}.
                            Tous droits réservés. Développé par <a href="https://gasycoder.com" target="_blank">GasyCoder</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
