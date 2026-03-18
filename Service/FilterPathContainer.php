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

namespace Liip\ImagineBundle\Service;

final class FilterPathContainer
{
    private string $target;

    /**
     * @param mixed[] $options
     */
    public function __construct(private string $source, string $target = '', private array $options = [])
    {
        $this->target = '' !== $target ? $target : $this->source;
    }

    public function createWebp(array $options): self
    {
        return new self(
            $this->source,
            $this->target.'.webp',
            [
                'format' => 'webp',
            ] + $options + $this->options
        );
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
