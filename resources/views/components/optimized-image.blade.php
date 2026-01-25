@if($fallbackUrl)
    {{-- Fallback image --}}
    <img 
        src="{{ $fallbackUrl }}" 
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        class="{{ $class }}"
    />
@elseif($responsive && $sizes && !empty($sizes))
    {{-- Responsive picture element --}}
    <picture>
        {{-- Different sources for different screen sizes --}}
        @foreach(array_reverse($sizes) as $sizeName => $sizeData)
            <source 
                srcset="{{ $sizeData['url'] }}" 
                media="(min-width: {{ $sizeData['width'] }}px)"
                type="image/webp"
            />
        @endforeach
        
        {{-- Fallback img tag --}}
        <img 
            src="{{ end($sizes)['url'] }}" 
            alt="{{ $alt }}"
            @if($width) width="{{ $width }}" @endif
            @if($height) height="{{ $height }}" @endif
            class="{{ $class }}"
            @if($lazy) loading="lazy" @endif
        />
    </picture>
@else
    {{-- Single optimized image --}}
    <img 
        src="{{ $mainUrl ?? asset('images/placeholder.png') }}" 
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        class="{{ $class }}"
        @if($lazy) loading="lazy" @endif
    />
@endif
