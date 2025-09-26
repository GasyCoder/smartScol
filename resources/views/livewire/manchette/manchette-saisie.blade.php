{{-- vue principale avec session flash --}}
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- En-tête -->
    <div class="mb-4">
        <h1 class="text-3xl font-bold font-heading text-gray-900 dark:text-white">
            Saisie des Manchettes
        </h1>
        <p class="mt-2 text-sm font-body text-gray-600 dark:text-gray-400">
            Session {{ ucfirst($sessionType) }} - Attribution des codes d'anonymat
        </p>
    </div>

    <!-- Breadcrumb de progression -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li class="flex items-center">
                    <button wire:click="backToStep('niveau')" 
                        class="flex items-center text-sm font-medium font-body {{ $step === 'niveau' ? 'text-primary-600 dark:text-primary-400' : ($niveauSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                        <svg class="flex-shrink-0 h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L9 5.414V17a1 1 0 102 0V5.414l5.293 5.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Niveau
                    </button>
                </li>
                
                @if($niveauSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('parcours')" 
                            class="ml-4 text-sm font-medium font-body {{ $step === 'parcours' ? 'text-primary-600 dark:text-primary-400' : ($parcoursSelected || empty($parcours) ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                            Parcours
                        </button>
                    </li>
                @endif
                
                @if($niveauSelected && ($parcoursSelected || empty($parcours)))
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('ec')" 
                            class="ml-4 text-sm font-medium font-body {{ $step === 'ec' ? 'text-primary-600 dark:text-primary-400' : ($ecSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                            EC
                        </button>
                    </li>
                @endif
                
                @if($ecSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-4 text-sm font-medium font-body {{ $step === 'setup' || $step === 'saisie' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $step === 'saisie' ? 'Saisie' : 'Configuration' }}
                        </span>
                    </li>
                @endif
            </ol>
        </nav>
    </div>

    <!-- Messages Flash Session -->
    @if(session('message'))
        @php
            $messageType = session('messageType', 'info');
        @endphp
        <div class="mb-6 p-4 rounded-lg border-l-4 {{ 
            $messageType === 'success' 
                ? 'bg-green-50 border-green-400 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                : ($messageType === 'error' 
                    ? 'bg-red-50 border-red-400 text-red-800 dark:bg-red-900/50 dark:text-red-300' 
                    : ($messageType === 'warning' 
                        ? 'bg-yellow-50 border-yellow-400 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' 
                        : 'bg-primary-50 border-primary-400 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300'
                    )
                ) 
            }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($messageType === 'success')
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'error')
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'warning')
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium font-body">{{ session('message') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" 
                                class="inline-flex rounded-md p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors duration-200">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Contenu principal selon l'étape -->
    <div class="bg-white dark:bg-gray-950 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800">
        @if($step === 'niveau')
            @include('livewire.manchette.partials.step-niveau')

        @elseif($step === 'parcours')
            @include('livewire.manchette.partials.step-parcours')

        @elseif($step === 'ec')
            @include('livewire.manchette.partials.step-ec')

        @elseif($step === 'setup')
            @include('livewire.manchette.partials.step-setup')

        @elseif($step === 'saisie')
            @include('livewire.manchette.partials.step-saisie')
        @endif
    </div>
</div>

