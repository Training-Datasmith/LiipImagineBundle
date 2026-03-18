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

use Liip\ImagineBundle\Config\Filter\Argument\Size;

/**
 * @codeCoverageIgnore
 */
final class Scale extends FilterAbstract
{
    public const NAME = 'scale';

    /**
     * @param float|null $to proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(private Size $dimensions, private ?float $to = null)
    {
    }

    public function getDimensions(): Size
    {
        return $this->dimensions;
    }

    public function getTo(): ?float
    {
        return $this->to;
    }
}
