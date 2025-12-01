<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class StatCard extends Component
{
    /**
     * Create a new component instance.
     */

    public $title;
    public $value;
    public $icon;
    public $color;
    public $link;
    
    public function __construct($title, $value, $icon = 'fa-chart-line', $color = 'primary', $link = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->color = $color;
        $this->link = $link;
    }

    public function render()
    {
        return view('components.stat-card');
    }
}