<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\Constraint;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ImageService
{
    /**
     * Standard responsive breakpoints
     */
    protected array $breakpoints = [
        'xs' => 320,
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ];

    /**
     * Generate optimized image at specific dimensions
     */
    public function generateOptimizedImage(string $sourcePath, int $width, int $height = null, int $quality = 85): ?string
    {
        try {
            $sourcePath = $this->normalizeSourcePath($sourcePath);
            
            if (!file_exists($sourcePath)) {
                return null;
            }

            $cacheKey = $this->generateCacheKey($sourcePath, $width, $height, $quality);
            $outputPath = $this->getOutputPath($cacheKey);
            $outputUrl = $this->getOutputUrl($cacheKey);

            if (file_exists($outputPath)) {
                return $outputUrl;
            }

            $this->resizeImage($sourcePath, $outputPath, $width, $height, $quality);
            
            return $outputUrl;
        } catch (\Exception $e) {
            Log::error('ImageService: Failed to process image', [
                'src' => $sourcePath,
                'width' => $width,
                'height' => $height,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate multiple sizes for responsive images
     */
    public function generateResponsiveSizes(string $sourcePath, int $baseWidth, int $baseHeight = null): array
    {
        $sizes = [];
        
        foreach ($this->breakpoints as $name => $breakpointWidth) {
            if ($breakpointWidth <= $baseWidth) {
                $height = $baseHeight ? (int)(($breakpointWidth / $baseWidth) * $baseHeight) : null;
                $url = $this->generateOptimizedImage($sourcePath, $breakpointWidth, $height);
                
                if ($url) {
                    $sizes[$name] = [
                        'url' => $url,
                        'width' => $breakpointWidth,
                        'height' => $height,
                    ];
                }
            }
        }

        // Add the base size if it's not in breakpoints
        if (empty($sizes) || $baseWidth > max($this->breakpoints)) {
            $url = $this->generateOptimizedImage($sourcePath, $baseWidth, $baseHeight);
            if ($url) {
                $sizes['base'] = [
                    'url' => $url,
                    'width' => $baseWidth,
                    'height' => $baseHeight,
                ];
            }
        }

        return $sizes;
    }

    /**
     * Resize and optimize image using Spatie Image
     */
    protected function resizeImage(string $sourcePath, string $outputPath, int $width, ?int $height, int $quality): void
    {
        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Load image using Spatie Image
        $image = Image::load($sourcePath);

        // Apply dimensions
        if ($width && $height) {
            // Both width and height specified - crop to fit
            $image->fit(Fit::Crop, $width, $height);
        } elseif ($width) {
            // Width only - preserve aspect ratio
            $image->width($width, [Constraint::PreserveAspectRatio]);
        } elseif ($height) {
            // Height only - preserve aspect ratio
            $image->height($height, [Constraint::PreserveAspectRatio]);
        }

        // Convert to WebP with quality
        $image->format('webp')
              ->quality(max(0, min(100, (int)$quality)))
              ->save($outputPath);

        // Optimize the image
        $this->optimizeImage($outputPath, $quality);
    }

    /**
     * Optimize image using Spatie ImageOptimizer
     */
    protected function optimizeImage(string $imagePath, int $quality): void
    {
        try {
            $optimizerChain = OptimizerChainFactory::create(['quality' => (int)$quality]);
            $optimizerChain->optimize($imagePath);
        } catch (\Exception $e) {
            Log::warning('ImageService: Image optimization failed', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            // Continue even if optimization fails - image is already saved
        }
    }

    /**
     * Normalize the source path to a full file system path
     */
    protected function normalizeSourcePath(string $src): string
    {
        // Remove query strings and fragments
        $src = strtok($src, '?#');
        $original = $src;

        // Try direct absolute path first
        if (file_exists($src)) {
            return $src;
        }

        // Clean the path
        $src = ltrim($src, '/');

        // Array of possible locations to check
        $possiblePaths = [
            public_path($src),
            public_path('images/' . $src),
            public_path('assets/' . $src),
            storage_path('app/public/' . $src),
            storage_path('app/public/images/' . $src),
            storage_path('app/public/uploads/' . $src),
        ];

        // Check each possible path
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Return public path as fallback
        return public_path($src);
    }

    /**
     * Generate a cache key based on source path and parameters
     */
    protected function generateCacheKey(string $sourcePath, int $width, ?int $height, int $quality): string
    {
        $filemtime = file_exists($sourcePath) ? filemtime($sourcePath) : 0;
        $params = [
            'path' => $sourcePath,
            'w' => $width,
            'h' => $height ?? 'auto',
            'q' => $quality,
            'mtime' => $filemtime,
        ];
        
        return 'img_' . md5(serialize($params));
    }

    /**
     * Get the output file path for the optimized image
     */
    protected function getOutputPath(string $cacheKey): string
    {
        $cacheDir = storage_path('app/public/optimized-images');
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        return $cacheDir . '/' . $cacheKey . '.webp';
    }

    /**
     * Get the URL for the optimized image
     */
    protected function getOutputUrl(string $cacheKey): string
    {
        return asset('storage/optimized-images/' . $cacheKey . '.webp');
    }

    /**
     * Get image info from path
     */
    public function getImageInfo(string $sourcePath): ?array
    {
        $sourcePath = $this->normalizeSourcePath($sourcePath);
        
        if (!file_exists($sourcePath)) {
            return null;
        }

        $imageInfo = getimagesize($sourcePath);
        
        if (!$imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'path' => $sourcePath,
        ];
    }
}
