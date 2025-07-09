<!-- VERSION ULTRA-COMPACTE : Une seule ligne avec dropdown -->
@if($presenceEnregistree && $presenceData)
<div class="mb-3 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
    <!-- Ligne principale compacte -->
    <div class="flex items-center justify-between px-3 py-2">
        <div class="flex items-center gap-3">
            <em class="text-green-600 ni ni-check-circle text-lg"></em>
            <span class="text-sm font-medium text-green-800 dark:text-green-200">
                Présence : {{ $presenceData->etudiants_presents }}P / {{ $presenceData->etudiants_absents }}A 
                ({{ $presenceData->taux_presence }}%)
            </span>
            <span class="px-2 py-0.5 text-xs bg-green-200 text-green-800 rounded dark:bg-green-800 dark:text-green-200">
                {{ $presenceData->session_libelle }}
            </span>
        </div>
        
        <div class="flex items-center gap-2">
            <button wire:click="openPresenceModal" 
                    class="p-1 text-green-700 hover:text-green-800 dark:text-green-300 hover:bg-green-100 rounded transition-colors duration-200">
                <em class="ni ni-edit text-sm"></em>
            </button>
            <button onclick="togglePresenceQuick()" 
                    class="p-1 text-green-700 hover:text-green-800 hover:bg-green-100 rounded transition-all duration-200">
                <em class="ni ni-chevron-down transition-transform duration-200 text-lg" id="quick-chevron"></em>
            </button>
        </div>
    </div>

    <!-- Détails rapides (cachés par défaut) -->
    <div class="hidden px-3 pb-2 border-t border-green-200 dark:border-green-700" id="quick-details">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
            <!-- Informations de base -->
            <div class="text-xs text-green-700 dark:text-green-300 space-y-1">
                <div><strong>Total:</strong> {{ $presenceData->total_etudiants }}/{{ $presenceData->total_attendu }}</div>
                <div><strong>Saisi le:</strong> {{ $presenceData->created_at->format('d/m H:i') }}</div>
                @if($presenceData->utilisateurSaisie)
                <div><strong>Par:</strong> {{ $presenceData->utilisateurSaisie->name }}</div>
                @endif
            </div>

            <!-- Barre de progression -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-green-700 dark:text-green-300">
                    <span><strong>Taux de présence</strong></span>
                    <span class="font-medium">{{ $presenceData->taux_presence }}%</span>
                </div>
                <div class="w-full h-2 bg-green-200 rounded-full dark:bg-green-800">
                    <div class="h-2 rounded-full transition-all duration-500
                        {{ $presenceData->taux_presence >= 75 ? 'bg-green-500' : 
                           ($presenceData->taux_presence >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                         style="width: {{ $presenceData->taux_presence }}%"></div>
                </div>
                @if($presenceData->ecart_attendu != 0)
                <div class="text-xs {{ $presenceData->ecart_attendu > 0 ? 'text-red-600' : 'text-orange-600' }}">
                    <strong>Écart:</strong> {{ $presenceData->ecart_attendu > 0 ? '+' : '' }}{{ $presenceData->ecart_attendu }}
                </div>
                @endif
            </div>

            <!-- Observations -->
            <div class="text-xs text-green-700 dark:text-green-300">
                @if($presenceData->observations)
                <div><strong>Observations:</strong></div>
                <div class="mt-1 p-2 bg-green-100 rounded text-green-800 dark:bg-green-800 dark:text-green-200">
                    {{ $presenceData->observations }}
                </div>
                @else
                <div class="text-gray-500 italic">Aucune observation</div>
                @endif
            </div>
        </div>

        <!-- Alerte si taux faible -->
        @if($presenceData->taux_presence < 50)
        <div class="mt-2 p-2 text-xs text-orange-800 bg-orange-100 rounded border border-orange-200 dark:bg-orange-900/20 dark:text-orange-200 dark:border-orange-800">
            <div class="flex items-center">
                <em class="mr-2 ni ni-alert-fill text-sm"></em>
                <span><strong>Attention:</strong> Taux de présence faible. Vérifiez avant de continuer.</span>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function togglePresenceQuick() {
    const details = document.getElementById('quick-details');
    const chevron = document.getElementById('quick-chevron');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
        
        // Animation slide down
        details.style.maxHeight = '0px';
        details.style.overflow = 'hidden';
        setTimeout(() => {
            details.style.maxHeight = details.scrollHeight + 'px';
        }, 10);
        
        setTimeout(() => {
            details.style.maxHeight = 'none';
            details.style.overflow = 'visible';
        }, 300);
    } else {
        details.style.maxHeight = details.scrollHeight + 'px';
        details.style.overflow = 'hidden';
        chevron.style.transform = 'rotate(0deg)';
        
        setTimeout(() => {
            details.style.maxHeight = '0px';
        }, 10);
        
        setTimeout(() => {
            details.classList.add('hidden');
            details.style.maxHeight = 'none';
            details.style.overflow = 'visible';
        }, 300);
    }
}

// Auto-expand si problème détecté
@if($presenceData->taux_presence < 50 || $presenceData->ecart_attendu != 0)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        togglePresenceQuick();
    }, 500);
});
@endif
</script>
@endif