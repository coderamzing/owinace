<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\ImageService;

class OptimizedImage extends Component
{
    public string $src;
    public string $alt;
    public ?int $width;
    public ?int $height;
    public int $quality;
    public string $class;
    public bool $lazy;
    public bool $responsive;
    
    public ?array $imageInfo = null;
    public ?array $sizes = null;
    public ?string $mainUrl = null;
    public ?string $fallbackUrl = null;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $src = '',
        string $alt = '',
        ?int $width = null,
        ?int $height = null,
        int $quality = 85,
        string $class = '',
        bool $lazy = true,
        bool $responsive = true
    ) {
        $this->src = $src;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
        $this->quality = $quality;
        $this->class = $class;
        $this->lazy = $lazy;
        $this->responsive = $responsive;
        
        $this->processImage();
    }

    /**
     * Process the image and generate URLs
     */
    protected function processImage(): void
    {
        $imageService = app(ImageService::class);
        
        // Get image info
        $this->imageInfo = $imageService->getImageInfo($this->src);
        
        if (!$this->imageInfo) {
            // Fallback if image doesn't exist
            $this->fallbackUrl = asset('images/placeholder.png');
            return;
        }
        
        // Use provided dimensions or fall back to original
        $targetWidth = $this->width ?? $this->imageInfo['width'];
        $targetHeight = $this->height ?? $this->imageInfo['height'];
        
        if ($this->responsive) {
            // Generate responsive sizes
            $this->sizes = $imageService->generateResponsiveSizes($this->src, $targetWidth, $targetHeight);
        } else {
            // Generate single size
            $this->mainUrl = $imageService->generateOptimizedImage($this->src, $targetWidth, $targetHeight, $this->quality);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.optimized-image');
    }
}
