{{-- resources/views/livewire/resultats/partials/dashboard-mega-paces.blade.php --}}

{{-- 🔧 CORRECTION : Toujours utiliser $statistiquesDetailes (mis à jour par la simulation) --}}
@php
    // ✅ Source unique : $statistiquesDetailes (contient TOUTES les données + MAJ par simulation)
    $stats = $statistiquesDetailes ?? [];
    
    // Données de base (JAMAIS modifiées par simulation)
    $totalInscrits = max((int)($stats['total_inscrits'] ?? 0), 1);
    $presents = (int)($stats['total_presents'] ?? 0);
    $absents = (int)($stats['total_absents'] ?? 0);
    $redoublants = (int)($stats['etudiants_redoublants'] ?? 0);
    $nouveaux = (int)($stats['etudiants_nouveaux'] ?? 0);
    
    // Décisions (MAJ par simulation si active)
    $admis = (int)($stats['admis'] ?? 0);
    $redo  = (int)($stats['redoublant_autorises'] ?? 0);
    $excl  = (int)($stats['exclus'] ?? 0);
    
    // ✅ Calcul pourcentages sur TOTAL INSCRITS
    $pAdmis   = $totalInscrits > 0 ? round(($admis / $totalInscrits) * 100, 1) : 0;
    $pRedo    = $totalInscrits > 0 ? round(($redo / $totalInscrits) * 100, 1) : 0;
    $pExcl    = $totalInscrits > 0 ? round(($excl / $totalInscrits) * 100, 1) : 0;
    $pAbsents = $totalInscrits > 0 ? round(($absents / $totalInscrits) * 100, 1) : 0;
    
    // ✅ Calcul pourcentages sur PRÉSENTS (plus précis pour les décisions)
    $pAdmisPresents = $presents > 0 ? round(($admis / $presents) * 100, 1) : 0;
    $pRedoPresents  = $presents > 0 ? round(($redo / $presents) * 100, 1) : 0;
    $pExclPresents  = $presents > 0 ? round(($excl / $presents) * 100, 1) : 0;
    
    // ✅ Vérification cohérence
    $totalDecisions = $admis + $redo + $excl;
    $coherent = $totalDecisions <= $presents;
@endphp

<div class="space-y-4" wire:key="dashboard-mega-{{ $resultatsVersion }}">
    {{-- Indicateur simulation active --}}
    @if($simulationEnCours)
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <div class="flex-1">
                    <div class="font-black text-lg">⚡ SIMULATION EN COURS</div>
                    <div class="text-sm opacity-90">Les décisions ci-dessous sont une prévisualisation basée sur vos critères</div>
                </div>
            </div>
        </div>
    @endif

    {{-- 📊 Méga tuiles XXL --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Inscrits --}}
        <div class="group relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs uppercase tracking-wider opacity-70 font-bold">Total Inscrits</div>
                    <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div class="text-5xl md:text-6xl font-black tracking-tight tabular-nums mb-2">{{ $totalInscrits }}</div>
                <div class="text-xs opacity-70">Présents: {{ $presents }} • Absents: {{ $absents }}</div>
            </div>
        </div>

        {{-- Admis --}}
        <div class="group relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer"
             wire:click="changerFiltre('admis')">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="text-xs uppercase tracking-wider opacity-80 font-bold">Admis</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20">{{ $pAdmisPresents }}%</span>
                    </div>
                    <svg class="w-8 h-8 opacity-20 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-5xl md:text-6xl font-black tracking-tight tabular-nums mb-2">{{ $admis }}</div>
                <div class="text-xs opacity-80">{{ $pAdmis }}% des inscrits • {{ $pAdmisPresents }}% des présents</div>
            </div>
        </div>

        {{-- Redoublants --}}
        <div class="group relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer"
             wire:click="changerFiltre('redoublant')">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="text-xs uppercase tracking-wider opacity-80 font-bold">Redoublants</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20">{{ $pRedoPresents }}%</span>
                    </div>
                    <svg class="w-8 h-8 opacity-20 group-hover:rotate-180 transition-transform duration-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-5xl md:text-6xl font-black tracking-tight tabular-nums mb-2">{{ $redo }}</div>
                <div class="text-xs opacity-80">{{ $pRedo }}% des inscrits • {{ $pRedoPresents }}% des présents</div>
            </div>
        </div>

        {{-- Exclus --}}
        <div class="group relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-rose-500 via-rose-600 to-red-700 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer"
             wire:click="changerFiltre('exclus')">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="text-xs uppercase tracking-wider opacity-80 font-bold">Exclus</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20">{{ $pExclPresents }}%</span>
                    </div>
                    <svg class="w-8 h-8 opacity-20 group-hover:rotate-90 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-5xl md:text-6xl font-black tracking-tight tabular-nums mb-2">{{ $excl }}</div>
                <div class="text-xs opacity-80">{{ $pExcl }}% des inscrits • {{ $pExclPresents }}% des présents</div>
            </div>
        </div>
    </div>

    {{-- 📊 Barre de répartition (BASE: présents uniquement) --}}
    <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 shadow-lg border-2 border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                Répartition des Présents
            </span>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
                {{ $presents }} présents / {{ $totalInscrits }} inscrits
            </span>
        </div>
        
        {{-- Barre visuelle (BASE: présents = 100%) --}}
        <div class="flex h-4 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 shadow-inner mb-3">
            @if($presents > 0)
                <button class="h-full bg-emerald-500 hover:bg-emerald-600 transition-all relative group" 
                        style="width: {{ $pAdmisPresents }}%" 
                        wire:click="changerFiltre('admis')"
                        title="Admis: {{ $admis }} ({{ $pAdmisPresents }}% des présents)">
                    @if($pAdmisPresents >= 10)
                        <span class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold">
                            {{ $pAdmisPresents }}%
                        </span>
                    @endif
                </button>
                <button class="h-full bg-amber-500 hover:bg-amber-600 transition-all relative group" 
                        style="width: {{ $pRedoPresents }}%" 
                        wire:click="changerFiltre('redoublant')"
                        title="Redoublants: {{ $redo }} ({{ $pRedoPresents }}% des présents)">
                    @if($pRedoPresents >= 10)
                        <span class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold">
                            {{ $pRedoPresents }}%
                        </span>
                    @endif
                </button>
                <button class="h-full bg-rose-500 hover:bg-rose-600 transition-all relative group" 
                        style="width: {{ $pExclPresents }}%" 
                        wire:click="changerFiltre('exclus')"
                        title="Exclus: {{ $excl }} ({{ $pExclPresents }}% des présents)">
                    @if($pExclPresents >= 10)
                        <span class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold">
                            {{ $pExclPresents }}%
                        </span>
                    @endif
                </button>
            @else
                <div class="h-full w-full bg-slate-400 flex items-center justify-center text-white text-xs font-bold">
                    Aucun présent
                </div>
            @endif
        </div>

        {{-- Légende détaillée --}}
        <div class="flex flex-wrap items-center justify-center gap-4 text-xs">
            <button wire:click="changerFiltre('admis')" class="flex items-center gap-1.5 hover:opacity-75 transition group">
                <div class="w-3 h-3 rounded-sm bg-emerald-500 group-hover:scale-110 transition-transform"></div>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $admis }}</span>
                <span class="text-slate-500 dark:text-slate-400">({{ $pAdmisPresents }}%) admis</span>
            </button>
            <button wire:click="changerFiltre('redoublant')" class="flex items-center gap-1.5 hover:opacity-75 transition group">
                <div class="w-3 h-3 rounded-sm bg-amber-500 group-hover:scale-110 transition-transform"></div>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $redo }}</span>
                <span class="text-slate-500 dark:text-slate-400">({{ $pRedoPresents }}%) redoublants</span>
            </button>
            <button wire:click="changerFiltre('exclus')" class="flex items-center gap-1.5 hover:opacity-75 transition group">
                <div class="w-3 h-3 rounded-sm bg-rose-500 group-hover:scale-110 transition-transform"></div>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $excl }}</span>
                <span class="text-slate-500 dark:text-slate-400">({{ $pExclPresents }}%) exclus</span>
            </button>
            <span class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-sm bg-slate-400 dark:bg-slate-600"></div>
                <span class="font-bold text-slate-600 dark:text-slate-400">{{ $absents }}</span>
                <span class="text-slate-500 dark:text-slate-400">({{ $pAbsents }}%) absents</span>
            </span>
        </div>
    </div>
</div>

{{-- CSS pour chiffres monospaces --}}
<style>
    .tabular-nums { font-variant-numeric: tabular-nums; }
</style>