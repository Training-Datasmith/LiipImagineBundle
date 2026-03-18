<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Filter\Type;

/**
 * @codeCoverageIgnore
 */
final class Watermark extends FilterAbstract
{
    public const NAME = 'watermark';

    public function __construct(
        private string $image,
        private string $position,
        /**
         * @var float
         */
        private ?float $size = null
    )
    {
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }
}
