{{-- resources/views/livewire/resultats/partials/dashboard-mega-paces.blade.php --}}

@php
    // ✅ Source unique : $statistiquesDetailes
    $stats = $statistiquesDetailes ?? [];
    
    // Données de base
    $totalInscrits = max((int)($stats['total_inscrits'] ?? 0), 1);
    $presents = (int)($stats['total_presents'] ?? 0);
    $absents = (int)($stats['total_absents'] ?? 0);
    $redoublants = (int)($stats['etudiants_redoublants'] ?? 0);
    $nouveaux = (int)($stats['etudiants_nouveaux'] ?? 0);
    
    // Décisions
    $admis = (int)($stats['admis'] ?? 0);
    $redo  = (int)($stats['redoublant_autorises'] ?? 0);
    $excl  = (int)($stats['exclus'] ?? 0);
    
    // Pourcentages sur PRÉSENTS (plus précis)
    $pAdmisPresents = $presents > 0 ? round(($admis / $presents) * 100, 1) : 0;
    $pRedoPresents  = $presents > 0 ? round(($redo / $presents) * 100, 1) : 0;
    $pExclPresents  = $presents > 0 ? round(($excl / $presents) * 100, 1) : 0;
@endphp

<div class="space-y-3" wire:key="dashboard-mega-{{ $resultatsVersion }}">
    
    {{-- 📊 TUILES COMPACTES AVEC CHIFFRES GÉANTS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        
        {{-- Total Inscrits --}}
        <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 p-4 shadow-lg hover:shadow-xl transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -mr-12 -mt-12"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-white/70">INSCRITS</span>
                    <svg class="w-5 h-5 text-white/20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div class="text-6xl lg:text-7xl font-black text-white tabular-nums leading-none mb-1">
                    {{ $totalInscrits }}
                </div>
                <div class="flex items-center gap-3 text-[11px] text-white/80 font-semibold">
                    <span>✓ {{ $presents }}</span>
                    <span>✗ {{ $absents }}</span>
                </div>
            </div>
        </div>

        {{-- Admis --}}
        <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-4 shadow-lg hover:shadow-xl transition-all cursor-pointer"
             wire:click="changerFiltre('admis')">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-white/80">ADMIS</span>
                    <div class="flex items-center gap-1">
                        <span class="text-2xl font-black text-white tabular-nums">{{ $pAdmisPresents }}</span>
                        <span class="text-sm font-black text-white/80">%</span>
                    </div>
                </div>
                <div class="text-6xl lg:text-7xl font-black text-white tabular-nums leading-none">
                    {{ $admis }}
                </div>
                <div class="mt-1 text-[11px] text-white/80 font-semibold">
                    sur {{ $presents }} présents
                </div>
            </div>
        </div>

        {{-- Redoublants --}}
        <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 shadow-lg hover:shadow-xl transition-all cursor-pointer"
             wire:click="changerFiltre('redoublant')">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-white/80">REDOUBLE</span>
                    <div class="flex items-center gap-1">
                        <span class="text-2xl font-black text-white tabular-nums">{{ $pRedoPresents }}</span>
                        <span class="text-sm font-black text-white/80">%</span>
                    </div>
                </div>
                <div class="text-6xl lg:text-7xl font-black text-white tabular-nums leading-none">
                    {{ $redo }}
                </div>
                <div class="mt-1 text-[11px] text-white/80 font-semibold">
                    sur {{ $presents }} présents
                </div>
            </div>
        </div>

        {{-- Exclus --}}
        <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-rose-500 to-red-700 p-4 shadow-lg hover:shadow-xl transition-all cursor-pointer"
             wire:click="changerFiltre('exclus')">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-white/80">EXCLUS</span>
                    <div class="flex items-center gap-1">
                        <span class="text-2xl font-black text-white tabular-nums">{{ $pExclPresents }}</span>
                        <span class="text-sm font-black text-white/80">%</span>
                    </div>
                </div>
                <div class="text-6xl lg:text-7xl font-black text-white tabular-nums leading-none">
                    {{ $excl }}
                </div>
                <div class="mt-1 text-[11px] text-white/80 font-semibold">
                    sur {{ $presents }} présents
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 RÉPARTITION VISUELLE MODERNE (Remplace la barre de progression) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        
        {{-- Card Admis % --}}
        <button wire:click="changerFiltre('admis')" 
                class="group relative bg-white dark:bg-slate-800 rounded-lg p-3 border-2 border-emerald-200 dark:border-emerald-800 hover:border-emerald-400 dark:hover:border-emerald-600 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Admis</span>
                </div>
                <div class="flex items-baseline gap-0.5">
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $pAdmisPresents }}</span>
                    <span class="text-sm font-black text-emerald-600/70 dark:text-emerald-400/70">%</span>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $admis }} étudiants</span>
                <svg class="w-4 h-4 text-emerald-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>

        {{-- Card Redoublants % --}}
        <button wire:click="changerFiltre('redoublant')"
                class="group relative bg-white dark:bg-slate-800 rounded-lg p-3 border-2 border-amber-200 dark:border-amber-800 hover:border-amber-400 dark:hover:border-amber-600 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Redouble</span>
                </div>
                <div class="flex items-baseline gap-0.5">
                    <span class="text-3xl font-black text-amber-600 dark:text-amber-400 tabular-nums">{{ $pRedoPresents }}</span>
                    <span class="text-sm font-black text-amber-600/70 dark:text-amber-400/70">%</span>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $redo }} étudiants</span>
                <svg class="w-4 h-4 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>

        {{-- Card Exclus % --}}
        <button wire:click="changerFiltre('exclus')"
                class="group relative bg-white dark:bg-slate-800 rounded-lg p-3 border-2 border-rose-200 dark:border-rose-800 hover:border-rose-400 dark:hover:border-rose-600 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Exclus</span>
                </div>
                <div class="flex items-baseline gap-0.5">
                    <span class="text-3xl font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ $pExclPresents }}</span>
                    <span class="text-sm font-black text-rose-600/70 dark:text-rose-400/70">%</span>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $excl }} étudiants</span>
                <svg class="w-4 h-4 text-rose-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>

        {{-- Card Absents --}}
        <div class="group relative bg-white dark:bg-slate-800 rounded-lg p-3 border-2 border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-slate-400 dark:bg-slate-600"></div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Absents</span>
                </div>
                <div class="flex items-baseline gap-0.5">
                    <span class="text-3xl font-black text-slate-600 dark:text-slate-400 tabular-nums">{{ round(($absents/$totalInscrits)*100, 1) }}</span>
                    <span class="text-sm font-black text-slate-600/70 dark:text-slate-400/70">%</span>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $absents }} étudiants</span>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- 📊 BARRE DE PROGRESSION SIMPLIFIÉE (optionnel, peut être supprimée) --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                Répartition visuelle
            </span>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-500">
                {{ $presents }} / {{ $totalInscrits }}
            </span>
        </div>
        <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-700">
            @if($presents > 0)
                <div class="bg-emerald-500 transition-all" 
                     style="width: {{ $pAdmisPresents }}%"
                     title="Admis: {{ $admis }}"></div>
                <div class="bg-amber-500 transition-all" 
                     style="width: {{ $pRedoPresents }}%"
                     title="Redoublants: {{ $redo }}"></div>
                <div class="bg-rose-500 transition-all" 
                     style="width: {{ $pExclPresents }}%"
                     title="Exclus: {{ $excl }}"></div>
            @endif
        </div>
    </div>
</div>

{{-- CSS pour chiffres monospaces --}}
<style>
    .tabular-nums { 
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
    }
</style>