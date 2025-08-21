<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public ?string $title;

    /**
     * Crée un nouveau composant.
     *
     * @param  string|null  $title
     */
    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }

    /**
     * Retourne la vue du composant.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
