{{-- Vue principale --}}
<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- VOTRE CODE EXISTANT pour le titre -->
             <div class="flex items-center space-x-3">
                @if($currentSessionType)
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $currentSessionType === 'Normale'
                            ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
                            : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700'
                        }}">
                        Session {{ $currentSessionType }}
                        @if($session_exam_id)
                            @php
                                $sessionInfo = App\Models\SessionExam::find($session_exam_id);
                            @endphp
                            @if($sessionInfo && $sessionInfo->date_debut)
                                <span class="ml-1 text-xs opacity-75">
                                    ({{ \Carbon\Carbon::parse($sessionInfo->date_debut)->format('d/m/Y') }})
                                </span>
                            @endif
                        @endif
                    </span>
                @endif
            </div>
            <!-- VOTRE CODE EXISTANT pour les actions globales -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('manchettes.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <em class="mr-1 icon ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>



    <!-- Liste des manchettes - VOTRE CODE EXISTANT -->
    @include('livewire.manchette.manchettes-table')



</div>
