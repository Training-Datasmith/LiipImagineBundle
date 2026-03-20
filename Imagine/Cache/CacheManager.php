<?php

declare(strict_types=1);

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Events\CacheResolveEvent;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\ImagineEvents;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

class CacheManager
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ResolverInterface[]
     */
    protected $resolvers = [];

    /**
     * @var string
     */
    protected $defaultResolver;

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param string $defaultResolver
     * @param bool   $webpGenerate
     */
    public function __construct(
        protected \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration $filterConfig,
        RouterInterface $router,
        protected \Liip\ImagineBundle\Imagine\Cache\SignerInterface $signer,
        protected \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher,
        $defaultResolver = null,
        private $webpGenerate = false
    ) {
        $this->router = $router;
        $this->defaultResolver = $defaultResolver ?: 'default';
    }

    /**
     * Adds a resolver to handle cached images for the given filter.
     *
     * If the resolver implements CacheManagerAwareInterface, the manager is injected
     * back into the resolver immediately after registration.
     *
     * @param string            $filter   Filter name or 'default' to set the global fallback resolver
     * @param ResolverInterface $resolver The resolver that stores/retrieves filtered image URLs
     *
     * @return void
     */
    public function addResolver(string $filter, ResolverInterface $resolver): void
    {
        $this->resolvers[$filter] = $resolver;

        if ($resolver instanceof CacheManagerAwareInterface) {
            $resolver->setCacheManager($this);
        }
    }

    /**
     * Returns the browser-accessible URL for a filtered image.
     *
     * If the filtered image is already cached (and WebP generation is disabled), the
     * cached URL is returned directly. Otherwise a filter action URL is generated so
     * the image will be processed on first access.
     *
     * @param string      $path          Source image path relative to the configured web root
     * @param string      $filter        Name of the filter set to apply
     * @param array       $runtimeConfig Optional per-request filter overrides; generates a signed runtime URL when non-empty
     * @param string|null $resolver      Resolver name override; null uses the filter's configured resolver
     * @param int         $referenceType URL reference type constant from UrlGeneratorInterface (default: ABSOLUTE_URL)
     *
     * @return string Browser-accessible URL for the filtered image
     */
    public function getBrowserPath(string $path, string $filter, array $runtimeConfig = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        if (!empty($runtimeConfig)) {
            $rcPath = $this->getRuntimePath($path, $runtimeConfig);

            return !$this->webpGenerate && $this->isStored($rcPath, $filter, $resolver) ?
                $this->resolve($rcPath, $filter, $resolver) :
                $this->generateUrl($path, $filter, $runtimeConfig, $resolver, $referenceType);
        }

        return !$this->webpGenerate && $this->isStored($path, $filter, $resolver) ?
            $this->resolve($path, $filter, $resolver) :
            $this->generateUrl($path, $filter, [], $resolver, $referenceType);
    }

    /**
     * Builds the internal cache path for a runtime-configured image.
     *
     * The path includes an HMAC signature so that arbitrary filter combinations
     * cannot be requested by anonymous users.
     *
     * @param string $path          Source image path (leading slash is stripped)
     * @param array  $runtimeConfig Per-request filter configuration that was applied
     *
     * @return string Internal path of the form "rc/{signature}/{path}"
     */
    public function getRuntimePath(string $path, array $runtimeConfig): string
    {
        $path = ltrim($path, '/');

        return 'rc/'.$this->signer->sign($path, $runtimeConfig).'/'.$path;
    }

    /**
     * Generates a Symfony route URL to the filter action for the given path.
     *
     * For static filters this routes to `liip_imagine_filter`. When runtime config
     * is provided the route is `liip_imagine_filter_runtime` with a signed hash.
     *
     * @param string      $path          Source image path
     * @param string      $filter        Filter set name
     * @param array       $runtimeConfig Optional per-request filter overrides; triggers runtime route generation when non-empty
     * @param string|null $resolver      Resolver name to embed in the route parameters
     * @param int         $referenceType UrlGeneratorInterface constant (ABSOLUTE_URL, ABSOLUTE_PATH, etc.)
     *
     * @return string Generated route URL
     */
    public function generateUrl(string $path, string $filter, array $runtimeConfig = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        $params = [
            'path' => ltrim($path, '/'),
            'filter' => $filter,
        ];

        if ($resolver) {
            $params['resolver'] = $resolver;
        }

        if (empty($runtimeConfig)) {
            return $this->router->generate('liip_imagine_filter', $params, $referenceType);
        }
        $params['filters'] = $runtimeConfig;
        $params['hash'] = $this->signer->sign($path, $runtimeConfig);

        return $this->router->generate('liip_imagine_filter_runtime', $params, $referenceType);
    }

    /**
     * Checks whether a filtered image is already stored in the resolver's cache.
     *
     * @param string      $path     Source image path
     * @param string      $filter   Filter set name
     * @param string|null $resolver Resolver name override; null uses the filter's configured resolver
     *
     * @return bool True when the filtered image exists in the cache
     */
    public function isStored(string $path, string $filter, ?string $resolver = null): bool
    {
        return $this->getResolver($filter, $resolver)->isStored($path, $filter);
    }

    /**
     * Resolves the cached URL for a filtered image.
     *
     * Dispatches PRE_RESOLVE and POST_RESOLVE events, allowing listeners to
     * rewrite the path or the resulting URL.
     *
     * @param string      $path     Source image path
     * @param string      $filter   Filter set name
     * @param string|null $resolver Resolver name override; null uses the filter's configured resolver
     *
     * @return string The browser-accessible URL of the cached filtered image
     *
     * @throws NotFoundHttpException If the path contains directory traversal sequences (/../)
     */
    public function resolve(string $path, string $filter, ?string $resolver = null): string
    {
        if (false !== mb_strpos($path, '/../') || 0 === mb_strpos($path, '../')) {
            throw new NotFoundHttpException(\sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        $preEvent = new CacheResolveEvent($path, $filter);
        $this->dispatchWithBC($preEvent, ImagineEvents::PRE_RESOLVE);

        $url = $this->getResolver($preEvent->getFilter(), $resolver)->resolve($preEvent->getPath(), $preEvent->getFilter());

        $postEvent = new CacheResolveEvent($preEvent->getPath(), $preEvent->getFilter(), $url);
        $this->dispatchWithBC($postEvent, ImagineEvents::POST_RESOLVE);

        return $postEvent->getUrl();
    }

    /**
     * Stores a filtered image binary in the resolver's cache.
     *
     * @param BinaryInterface $binary   The filtered image binary with its MIME type
     * @param string          $path     Source image path used as the cache key
     * @param string          $filter   Filter set name used as part of the cache key
     * @param string|null     $resolver Resolver name override; null uses the filter's configured resolver
     *
     * @return void
     *
     * @see ResolverInterface::store
     */
    public function store(BinaryInterface $binary, string $path, string $filter, ?string $resolver = null): void
    {
        $this->getResolver($filter, $resolver)->store($binary, $path, $filter);
    }

    /**
     * @param string|string[]|null $paths
     * @param string|string[]|null $filters
     */
    public function remove($paths = null, $filters = null): void
    {
        if (null === $filters) {
            $filters = array_keys($this->filterConfig->all());
        } elseif (!\is_array($filters)) {
            $filters = [$filters];
        }
        if (!\is_array($paths)) {
            $paths = [$paths];
        }

        $paths = array_filter($paths);
        $filters = array_filter($filters);

        $mapping = new \SplObjectStorage();
        foreach ($filters as $filter) {
            $resolver = $this->getResolver($filter, null);

            $list = $mapping[$resolver] ?? [];

            $list[] = $filter;

            $mapping[$resolver] = $list;
        }

        foreach ($mapping as $resolver) {
            $resolver->remove($paths, $mapping[$resolver]);
        }
    }

    /**
     * Gets a resolver for the given filter.
     *
     * In case there is no specific resolver, but a default resolver has been configured, the default will be returned.
     *
     * @param string $filter
     * @param string $resolver
     *
     * @throws \OutOfBoundsException If neither a specific nor a default resolver is available
     *
     * @return ResolverInterface
     */
    protected function getResolver($filter, $resolver)
    {
        // BC
        if (!$resolver) {
            $config = $this->filterConfig->get($filter);

            $resolverName = empty($config['cache']) ? $this->defaultResolver : $config['cache'];
        } else {
            $resolverName = $resolver;
        }

        if (!isset($this->resolvers[$resolverName])) {
            throw new \OutOfBoundsException(\sprintf('Could not find resolver "%s" for "%s" filter type', $resolverName, $filter));
        }

        return $this->resolvers[$resolverName];
    }

    /**
     * BC Layer for Symfony < 4.3
     */
    private function dispatchWithBC(CacheResolveEvent $event, string $eventName): void
    {
        if ($this->dispatcher instanceof ContractsEventDispatcherInterface) {
            $this->dispatcher->dispatch($event, $eventName);
        } else {
            $this->dispatcher->dispatch($eventName, $event);
        }
    }
}
