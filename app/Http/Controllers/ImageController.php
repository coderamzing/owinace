<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Services\ImageService;

class ImageController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Serve optimized image at URL: /images/{width}x{height}/{path}
     * or /images/{width}/{path} for auto-height
     */
    public function serve(Request $request, string $dimensions, string $path)
    {
        // Parse dimensions (e.g., "800x600" or "800")
        $parts = explode('x', $dimensions);
        $width = (int)$parts[0];
        $height = isset($parts[1]) ? (int)$parts[1] : null;

        // Get quality from query param
        $quality = (int)$request->query('q', 85);

        // Decode the path (in case it has URL encoding)
        $path = urldecode($path);

        // Generate or get cached image
        $outputUrl = $this->imageService->generateOptimizedImage($path, $width, $height, $quality);

        if (!$outputUrl) {
            // Return placeholder or 404
            abort(404, 'Image not found');
        }

        // Convert URL to file path
        $outputPath = str_replace(asset('storage/optimized-images/'), storage_path('app/public/optimized-images/'), $outputUrl);

        if (!file_exists($outputPath)) {
            abort(404, 'Optimized image not found');
        }

        // Serve the image
        $file = file_get_contents($outputPath);
        $type = 'image/webp';

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }
}
