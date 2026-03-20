<?php

declare(strict_types=1);

/**
 * LiipImagineBundle — generate browser-accessible image URLs example.
 *
 * In real Symfony applications the CacheManager is accessed via DI.
 * This example shows the runtime API usage pattern.
 *
 * Typical usage in a Twig template:
 *   {{ '/uploads/product.jpg' | imagine_filter('thumbnail_300x300') }}
 *
 * Typical usage in PHP:
 *   $url = $cacheManager->getBrowserPath('/uploads/product.jpg', 'thumbnail_300x300');
 *
 * --- Service registration example (services.yaml) ---
 *
 * # liip_imagine.yaml
 * liip_imagine:
 *   filter_sets:
 *     thumbnail_300x300:
 *       filters:
 *         thumbnail: { size: [300, 300], mode: outbound }
 *     product_zoom:
 *       filters:
 *         relative_resize: { widen: 800 }
 *         strip: ~
 *
 * --- Controller usage ---
 *
 * use Liip\ImagineBundle\Imagine\Cache\CacheManager;
 *
 * class ProductController
 * {
 *     public function show(CacheManager $cacheManager, string $imagePath): Response
 *     {
 *         // Returns cached URL or filter action URL if not yet cached
 *         $thumbnailUrl = $cacheManager->getBrowserPath($imagePath, 'thumbnail_300x300');
 *
 *         // Runtime override: apply a custom width without a named filter set
 *         $zoomedUrl = $cacheManager->getBrowserPath($imagePath, 'product_zoom', [
 *             'relative_resize' => ['widen' => 1200],
 *         ]);
 *
 *         return $this->render('product/show.html.twig', [
 *             'thumbnail' => $thumbnailUrl,
 *             'zoomed'    => $zoomedUrl,
 *         ]);
 *     }
 *
 *     public function removeCache(CacheManager $cacheManager, string $imagePath): Response
 *     {
 *         // Remove all cached variants of an image (e.g., after image replacement)
 *         $cacheManager->remove($imagePath);
 *
 *         return new Response('Cache cleared.', 200);
 *     }
 * }
 */

echo 'See the docblock above for LiipImagineBundle usage patterns.' . PHP_EOL;
echo 'This bundle requires a running Symfony kernel and cannot be demoed standalone.' . PHP_EOL;
