<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\Resultats\ListeResultatsPACES;

class TestExportPaces extends Command
{
    protected $signature = 'test:export-paces';
    protected $description = 'Tester les méthodes export PACES';

    public function handle()
    {
        $this->info('🔍 Test des méthodes export PACES');
        
        $component = new ListeResultatsPACES();
        
        $this->info('Classe : ' . get_class($component));
        
        // Toutes les méthodes publiques
        $methods = get_class_methods($component);
        $exportMethods = array_filter($methods, fn($m) => str_contains(strtolower($m), 'export'));
        
        $this->info('Méthodes export trouvées :');
        foreach ($exportMethods as $method) {
            $this->line("  ✅ {$method}");
        }
        
        // Test spécifique
        if (method_exists($component, 'exporterExcelPaces')) {
            $this->info('✅ exporterExcelPaces EXISTE');
        } else {
            $this->error('❌ exporterExcelPaces N\'EXISTE PAS');
        }
        
        if (method_exists($component, 'exporterPdfPaces')) {
            $this->info('✅ exporterPdfPaces EXISTE');
        } else {
            $this->error('❌ exporterPdfPaces N\'EXISTE PAS');
        }
        
        // Afficher le fichier source
        $reflection = new \ReflectionClass($component);
        $this->info('Fichier source : ' . $reflection->getFileName());
        
        return 0;
    }
}