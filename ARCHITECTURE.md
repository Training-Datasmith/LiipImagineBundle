# Architecture: LiipImagineBundle

## Purpose

Symfony bundle for on-the-fly image processing and caching. It applies configurable filter sets (resize, crop, watermark, etc.) to images and stores the results in a configurable cache backend.

## Directory Structure

```
Imagine/
  Cache/
    Resolver/         Storage backends: WebPath, AwsS3, Flysystem, PSR cache proxy
    CacheManager.php  Central coordinator for resolve/store/remove operations
    Signer.php        HMAC URL signer to prevent filter enumeration attacks
  Data/
    DataManager.php   Loads source binary from configured data loaders (filesystem, Flysystem, etc.)
  Filter/
    FilterManager.php Applies ordered filter sets to an image binary
    Loader/           Individual filter loaders (thumbnail, crop, rotate, grayscale, etc.)
    PostProcessor/    Runs external tools (jpegoptim, pngquant, cwebp) after filtering
  Binary/             Immutable image binary value object with MIME type
Controller/           Handles dynamic filter URLs and redirects
```

## Key Design Decisions

- **Filter chains**: A filter set is a named ordered list of filters defined in config. Each filter loader receives and returns an Imagine image object.
- **Signed URLs**: The `Signer` creates HMAC tokens in cache URLs so anonymous users cannot enumerate arbitrary filter sets against arbitrary images.
- **Pluggable resolvers**: Cache resolvers are services tagged `liip_imagine.cache.resolver`; applications can store filtered images anywhere.
- **Post-processors**: External binary optimisers run after filtering but before caching, avoiding any PHP-level image quality loss.

## Extension Points

- Tag a service `liip_imagine.filter.loader` to register a custom filter.
- Tag a service `liip_imagine.cache.resolver` to register a custom storage backend.
- Tag a service `liip_imagine.filter.post_processor` to add optimisation steps.
- Tag a service `liip_imagine.binary.loader` to load source images from custom sources.

## Dependency Flow

```
HTTP GET /media/cache/{filter}/{path}
  -> CacheManager::getBrowserPath()
    -> CacheResolver::isStored()?
      yes -> redirect to cached URL
      no  -> DataManager::find(path)
           -> FilterManager::applyFilter(binary, filter_set)
           -> PostProcessor chain
           -> CacheResolver::store()
           -> redirect
```
