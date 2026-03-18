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

namespace Liip\ImagineBundle\Config\Filter\Type;

use Liip\ImagineBundle\Config\Filter\Argument\Size;

/**
 * @codeCoverageIgnore
 */
final class Downscale extends FilterAbstract
{
    public const NAME = 'downscale';

    /**
     * @param float|null $by sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(private ?Size $max = null, private ?float $by = null)
    {
    }

    public function getMax(): ?Size
    {
        return $this->max;
    }

    public function getBy(): ?float
    {
        return $this->by;
    }
}
