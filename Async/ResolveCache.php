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

namespace Liip\ImagineBundle\Async;

use Enqueue\Util\JSON;
use Liip\ImagineBundle\Exception\LogicException;

/**
 * @deprecated Use Liip\ImagineBundle\Message\WarmupCache instead
 */
class ResolveCache implements \JsonSerializable
{
    /**
     * @param string[]|null $filters
     */
    public function __construct(private string $path, private ?array $filters = null, private bool $force = false)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]|null
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function jsonSerialize(): array
    {
        return ['path' => $this->path, 'filters' => $this->filters, 'force' => $this->force];
    }

    public static function jsonDeserialize(string $json): self
    {
        $data = array_replace(['path' => null, 'filters' => null, 'force' => false], JSON::decode($json));

        if (!$data['path']) {
            throw new LogicException('The message does not contain "path" but it is required.');
        }

        if (!(null === $data['filters'] || \is_array($data['filters']))) {
            throw new LogicException('The message filters could be either null or array.');
        }

        return new static($data['path'], $data['filters'], $data['force']);
    }
}
