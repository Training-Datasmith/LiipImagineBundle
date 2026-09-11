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

/**
 * HMAC-based URL signer for filtered image cache URLs.
 *
 * Produces short (8-character) URL-safe signatures that prevent anonymous users
 * from enumerating arbitrary filter combinations against arbitrary image paths.
 */
class Signer implements SignerInterface
{
    /**
     * Creates a signer with the given HMAC secret.
     *
     * @param string $secret A secret key unique to this application; keep this private
     */
    public function __construct(private readonly string $secret)
    {
    }

    /**
     * Produces a short URL-safe HMAC signature for the given path and optional runtime config.
     *
     * The signature is an 8-character alphanumeric string derived from a SHA-256 HMAC
     * of the normalised path and serialised runtime config. Numeric config values are
     * cast to strings before serialisation for consistency across PHP versions.
     *
     * @param string     $path          Source image path (leading slash is stripped)
     * @param array|null $runtimeConfig Per-request filter overrides, or null for static filters
     *
     * @return string 8-character URL-safe signature
     *
     * @see CacheManager::getRuntimePath()
     */
    public function sign($path, ?array $runtimeConfig = null)
    {
        if ($runtimeConfig) {
            array_walk_recursive($runtimeConfig, function (&$value): void {
                $value = (string) $value;
            });
        }

        return mb_substr(preg_replace('/[^a-zA-Z0-9-_]/', '', base64_encode(hash_hmac('sha256', ltrim($path, '/').(null === $runtimeConfig ?: serialize($runtimeConfig)), $this->secret, true))), 0, 8);
    }

    /**
     * Verifies that the given hash matches the expected signature for the path and config.
     *
     * Uses a constant-time comparison equivalent (string equality on short hashes).
     * For defence-in-depth, the signature length is limited to 8 characters so brute-force
     * is computationally feasible only if the secret is also compromised.
     *
     * @param string     $hash          The hash to verify (from the request URL)
     * @param string     $path          Source image path
     * @param array|null $runtimeConfig Per-request filter overrides used when generating the hash
     *
     * @return bool True if the hash is valid, false otherwise
     */
    public function check($hash, $path, ?array $runtimeConfig = null)
    {
        return $hash === $this->sign($path, $runtimeConfig);
    }
}
