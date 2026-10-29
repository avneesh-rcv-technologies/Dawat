<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Cache Testing</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
        }

        .controls {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        button {
            padding: 12px 24px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-refresh {
            background: #667eea;
            color: white;
        }

        .btn-refresh:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-clear {
            background: #ff6b6b;
            color: white;
        }

        .btn-clear:hover {
            background: #ee5a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .image-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
        }

        .image-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .image-wrapper {
            width: 100%;
            height: 160px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .image-card:hover .image-wrapper img {
            transform: scale(1.05);
        }

        .image-load-time {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .load-time-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .cache-badge {
            display: inline-block;
            background: #51cf66;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 700;
            margin-left: 5px;
        }

        .cache-badge.miss {
            background: #ffa94d;
        }

        .performance-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 8px;
            margin-top: 20px;
            overflow: hidden;
            display: flex;
        }

        .performance-bar-segment {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
            transition: flex 0.3s ease;
        }

        .performance-bar-segment.cached {
            background: linear-gradient(90deg, #51cf66, #37b24d);
        }

        .performance-bar-segment.missed {
            background: linear-gradient(90deg, #ffa94d, #fd7e14);
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #999;
        }

        .image-info {
            padding: 15px;
        }

        .image-id {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }

        .image-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .instructions {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .instructions h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .instructions ol {
            margin-left: 20px;
            color: #666;
            line-height: 1.6;
        }

        .instructions li {
            margin-bottom: 8px;
        }

        .timestamp {
            color: #999;
            font-size: 12px;
            text-align: center;
            margin-top: 10px;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .no-images {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-images svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 15px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 20px;
            }

            .stat-card .value {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Instructions -->
        <div class="instructions">
            <h3>📋 Cache Testing Guide</h3>
            <ol>
                <li><strong>First Load:</strong> Click "Refresh" to load images. Page cache shows retrieval time, browser cache downloads images.</li>
                <li><strong>Second Load (Same Browser Tab):</strong> Images load from browser cache (nearly instant), page load time minimal.</li>
                <li><strong>Clear Browser Cache:</strong> Use "Clear All Cache" then refresh browser (Ctrl+Shift+Del) to reset everything.</li>
                <li><strong>Performance Metrics:</strong>
                    <ul style="margin-left: 20px; margin-top: 8px;">
                        <li><strong>Page Load Time:</strong> Laravel query + image metadata retrieval</li>
                        <li><strong>Image Load Time:</strong> Individual image download time (shown per image)</li>
                    </ul>
                </li>
                <li><strong>Optimization Tips:</strong> Images are compressed, lazy-loaded, and browser-cached for 1 hour.</li>
        <!-- Header with Stats -->
        <div class="header">
            <h1>🖼️ Restaurant Image Cache Testing</h1>
            <p style="color: #666; margin-top: 5px;">Monitor cache performance and image loading efficiency</p>

            <div class="stats">
                <div class="stat-card">
                    <div class="label">Total Images</div>
                    <div class="value">{{ count($images) }}</div>
                </div>
                <div class="stat-card">
                    <div class="label">Page Load Time</div>
                    <div class="value">{{ $loadTime }}ms</div>
                </div>
                <div class="stat-card">
                    <div class="label">Images Loaded</div>
                    <div class="value">{{ count(array_filter($images->pluck('url')->toArray())) }}/{{ count($images) }}</div>
                </div>
                <div class="stat-card">
                    <div class="label">Server Time</div>
                    <div class="value">{{ now()->format('H:i:s') }}</div>
                </div>
                <div class="stat-card">
                    <div class="label">🚀 Cache Hits</div>
                    <div class="value"><span id="cache-hits">0</span>/{{ count($images) }}</div>
                </div>
            </div>

            <!-- Performance Bar -->
            <div style="margin-top: 20px;">
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Cache Performance</p>
                <div class="performance-bar">
                    <div class="performance-bar-segment cached" id="cached-segment" style="flex: 0;">
                        <span id="cached-percent">0%</span>
                    </div>
                    <div class="performance-bar-segment missed" id="missed-segment" style="flex: 1;">
                        <span id="missed-percent">100%</span>
                    </div>
                </div>
            </div>

            <div class="controls">
                <button class="btn-refresh" onclick="location.reload()">
                    🔄 Refresh Page
                </button>
                <button class="btn-clear" onclick="clearCache()">
                    🗑️ Clear All Cache
                </button>
            </div>

            <div class="timestamp">Last updated: {{ now()->format('Y-m-d H:i:s') }}</div>
        </div>

        <!-- Image Gallery -->
        <div>
            @if(count($images) > 0)
                <div class="gallery">
                    @foreach($images as $item)
                        <div class="image-card">
                            <div class="image-wrapper">
                                @if($item['url'])
                                    <img src="{{ $item['url'] }}" alt="{{ $item['name'] }}" loading="lazy">
                                @else
                                    <div class="image-placeholder">
                                        📷 No Image
                                    </div>
                                @endif
                            </div>
                            <div class="image-info">
                                <div class="image-id">ID: {{ $item['id'] }}</div>
                                <div class="image-name">{{ $item['name'] }}</div>
                                <div class="image-load-time" id="load-time-{{ $item['id'] }}">⏳ Loading...</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-images">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>No images found</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        let cacheHits = 0;
        let totalImages = 0;

        // Track individual image load times and cache status
        function trackImageLoad(img, restaurantId) {
            const startTime = performance.now();
            totalImages++;
            
            img.onload = function() {
                const loadTime = Math.round(performance.now() - startTime);
                const badge = document.getElementById(`load-time-${restaurantId}`);
                
                // Detect if image was served from browser cache
                // Images from cache typically load in < 5ms
                const isFromCache = loadTime < 5;
                
                if (isFromCache) {
                    cacheHits++;
                    if (badge) {
                        badge.innerHTML = `<span class="load-time-badge">⚡ ${loadTime}ms</span> <span class="cache-badge">💾 CACHED</span>`;
                    }
                } else {
                    if (badge) {
                        badge.innerHTML = `<span class="load-time-badge">⚡ ${loadTime}ms</span> <span class="cache-badge miss">🌐 NETWORK</span>`;
                    }
                }
                
                updatePerformanceBar();
            };
            
            img.onerror = function() {
                const badge = document.getElementById(`load-time-${restaurantId}`);
                if (badge) {
                    badge.innerHTML = '<span class="load-time-badge" style="background: #ff6b6b;">❌ Failed</span>';
                }
            };
        }

        // Update performance bar with cache hit ratio
        function updatePerformanceBar() {
            const cachePercentage = totalImages > 0 ? Math.round((cacheHits / totalImages) * 100) : 0;
            const missPercentage = 100 - cachePercentage;
            
            document.getElementById('cache-hits').textContent = cacheHits;
            document.getElementById('cached-percent').textContent = cachePercentage + '%';
            document.getElementById('missed-percent').textContent = missPercentage + '%';
            
            document.getElementById('cached-segment').style.flex = cachePercentage || 0;
            document.getElementById('missed-segment').style.flex = missPercentage || 1;
        }

        // Initialize image load tracking
        document.querySelectorAll('.image-wrapper img').forEach(img => {
            const restaurantId = img.closest('.image-card').querySelector('[id^="load-time-"]')?.id.split('-')[2];
            if (restaurantId) {
                trackImageLoad(img, restaurantId);
            }
        });

        function clearCache() {
            if (confirm('This will clear:\n✓ Laravel data cache\n✓ Image metadata cache\n\nYou should also clear Browser Cache (Ctrl+Shift+Del) for full reset.')) {
                fetch('{{ route("clear.cache") }}')
                    .then(response => response.json())
                    .then(data => {
                        alert('✅ Server cache cleared!\n\nNow clear browser cache:\nCtrl+Shift+Del → Clear All Time');
                        location.reload();
                    })
                    .catch(error => {
                        alert('Error clearing cache: ' + error);
                    });
            }
        }

        // Auto-refresh for continuous testing (optional)
        // Uncomment to auto-refresh every 5 seconds
        // setInterval(() => location.reload(), 5000);
    </script>
</body>

</html>