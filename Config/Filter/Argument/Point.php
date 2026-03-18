<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Filter\Argument;

/**
 * @codeCoverageIgnore
 */
final class Point
{
    public function __construct(
        /**
         * @var int
         */
        private ?int $x = null,
        /**
         * @var int
         */
        private ?int $y = null
    )
    {
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function getY(): ?int
    {
        return $this->y;
    }
}
