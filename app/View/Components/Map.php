<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Map extends Component
{
    public float $lat;
    public float $lng;
    public int $zoom;
    public string $height;
    public string $width;
    public array $markers;
    public string $id;

    /**
     * Create a new component instance.
     */
    public function __construct(
        float $lat = 10.0,
        float $lng = 6.5,
        int $zoom = 7,
        string $height = '400px',
        string $width = '100%',
        array $markers = [],
        string $id = 'map'
    ) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->zoom = $zoom;
        $this->height = $height;
        $this->width = $width;
        $this->markers = $markers;
        $this->id = $id;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.map');
    }
}
