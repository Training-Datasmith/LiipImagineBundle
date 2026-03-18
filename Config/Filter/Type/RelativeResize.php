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
final class RelativeResize extends FilterAbstract
{
    public const NAME = 'relative_resize';

    public function __construct(
        /**
         * @var float
         */
        private ?float $heighten = null,
        /**
         * @var float
         */
        private ?float $widen = null,
        /**
         * @var float
         */
        private ?float $increase = null,
        /**
         * @var float
         */
        private ?float $scale = null
    )
    {
    }

    public function getHeighten(): ?float
    {
        return $this->heighten;
    }

    public function getWiden(): ?float
    {
        return $this->widen;
    }

    public function getIncrease(): ?float
    {
        return $this->increase;
    }

    public function getScale(): ?float
    {
        return $this->scale;
    }
}
