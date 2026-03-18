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

class Signer implements SignerInterface
{
    /**
     * @param string $secret
     */
    public function __construct(private $secret)
    {
    }

    public function sign($path, ?array $runtimeConfig = null): string
    {
        if ($runtimeConfig) {
            array_walk_recursive($runtimeConfig, function (&$value): void {
                $value = (string) $value;
            });
        }

        return mb_substr(preg_replace('/[^a-zA-Z0-9-_]/', '', base64_encode(hash_hmac('sha256', ltrim($path, '/').(null === $runtimeConfig ?: serialize($runtimeConfig)), $this->secret, true))), 0, 8);
    }

    public function check($hash, $path, ?array $runtimeConfig = null): bool
    {
        return $hash === $this->sign($path, $runtimeConfig);
    }
}
