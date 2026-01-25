<?php

if (!function_exists('optimized_image')) {
    /**
     * Generate an optimized image URL
     * 
     * @param string $path Path to the source image
     * @param int $width Target width
     * @param int|null $height Target height (optional)
     * @param int $quality Image quality (0-100)
     * @return string|null Optimized image URL
     */
    function optimized_image(string $path, int $width, ?int $height = null, int $quality = 85): ?string
    {
        return app(\App\Services\ImageService::class)->generateOptimizedImage($path, $width, $height, $quality);
    }
}

if (!function_exists('image_url')) {
    /**
     * Generate image URL using URL pattern
     * 
     * @param string $path Path to the source image
     * @param int $width Target width
     * @param int|null $height Target height (optional)
     * @param int $quality Image quality (0-100)
     * @return string Image URL
     */
    function image_url(string $path, int $width, ?int $height = null, int $quality = 85): string
    {
        $dimensions = $height ? "{$width}x{$height}" : $width;
        $url = url("/images/{$dimensions}/{$path}");
        
        if ($quality !== 85) {
            $url .= "?q={$quality}";
        }
        
        return $url;
    }
}

if (!function_exists('responsive_image_urls')) {
    /**
     * Generate responsive image URLs for different breakpoints
     * 
     * @param string $path Path to the source image
     * @param int $maxWidth Maximum width
     * @param int|null $maxHeight Maximum height (optional)
     * @return array Array of image URLs with breakpoints
     */
    function responsive_image_urls(string $path, int $maxWidth, ?int $maxHeight = null): array
    {
        return app(\App\Services\ImageService::class)->generateResponsiveSizes($path, $maxWidth, $maxHeight);
    }
}
