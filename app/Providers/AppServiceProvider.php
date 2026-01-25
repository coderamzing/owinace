<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Portfolio;
use App\Models\Team;
use App\Models\User;
use App\Observers\LeadObserver;
use App\Observers\PortfolioObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\ImageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Lead::observe(LeadObserver::class);
        Team::observe(TeamObserver::class);
        User::observe(UserObserver::class);
        Portfolio::observe(PortfolioObserver::class);
        
        // Register custom Blade directives for optimized images
        
        // Simple URL generator
        // Usage: @imgUrl('/path/to/image.jpg', 800, 600)
        Blade::directive('imgUrl', function ($expression) {
            return "<?php echo app(App\Services\ImageService::class)->generateOptimizedImage({$expression}); ?>";
        });
        
        // Full image tag with all options
        // Usage: @img(['src' => '/path/to/image.jpg', 'w' => 800, 'h' => 600, 'alt' => 'Description', 'class' => 'my-class'])
        Blade::directive('img', function ($expression) {
            return "<?php echo (function(\$params) {
                \$src = \$params['src'] ?? \$params['path'] ?? '';
                \$width = \$params['w'] ?? \$params['width'] ?? null;
                \$height = \$params['h'] ?? \$params['height'] ?? null;
                \$quality = \$params['q'] ?? \$params['quality'] ?? 85;
                \$alt = \$params['alt'] ?? '';
                \$class = \$params['class'] ?? '';
                \$lazy = \$params['lazy'] ?? true;
                \$responsive = \$params['responsive'] ?? false;
                
                \$imageService = app(App\Services\ImageService::class);
                
                if (\$responsive) {
                    \$imageInfo = \$imageService->getImageInfo(\$src);
                    if (!\$imageInfo) {
                        return '<img src=\"' . asset('images/placeholder.png') . '\" alt=\"' . htmlspecialchars(\$alt) . '\" />';
                    }
                    
                    \$targetWidth = \$width ?? \$imageInfo['width'];
                    \$targetHeight = \$height ?? \$imageInfo['height'];
                    \$sizes = \$imageService->generateResponsiveSizes(\$src, \$targetWidth, \$targetHeight);
                    
                    if (empty(\$sizes)) {
                        return '<img src=\"' . asset('images/placeholder.png') . '\" alt=\"' . htmlspecialchars(\$alt) . '\" />';
                    }
                    
                    \$html = '<picture>';
                    foreach (array_reverse(\$sizes) as \$sizeData) {
                        \$html .= '<source srcset=\"' . \$sizeData['url'] . '\" media=\"(min-width: ' . \$sizeData['width'] . 'px)\" type=\"image/webp\" />';
                    }
                    \$lastSize = end(\$sizes);
                    \$html .= '<img src=\"' . \$lastSize['url'] . '\" alt=\"' . htmlspecialchars(\$alt) . '\"';
                    if (\$width) \$html .= ' width=\"' . \$width . '\"';
                    if (\$height) \$html .= ' height=\"' . \$height . '\"';
                    if (\$class) \$html .= ' class=\"' . htmlspecialchars(\$class) . '\"';
                    if (\$lazy) \$html .= ' loading=\"lazy\"';
                    \$html .= ' />';
                    \$html .= '</picture>';
                    
                    return \$html;
                } else {
                    \$url = \$imageService->generateOptimizedImage(\$src, \$width, \$height, \$quality);
                    if (!\$url) {
                        \$url = asset('images/placeholder.png');
                    }
                    
                    \$html = '<img src=\"' . \$url . '\" alt=\"' . htmlspecialchars(\$alt) . '\"';
                    if (\$width) \$html .= ' width=\"' . \$width . '\"';
                    if (\$height) \$html .= ' height=\"' . \$height . '\"';
                    if (\$class) \$html .= ' class=\"' . htmlspecialchars(\$class) . '\"';
                    if (\$lazy) \$html .= ' loading=\"lazy\"';
                    \$html .= ' />';
                    
                    return \$html;
                }
            })({$expression}); ?>";
        });
    }
}
