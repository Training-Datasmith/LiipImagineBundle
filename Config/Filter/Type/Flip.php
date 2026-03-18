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

/**
 * @codeCoverageIgnore
 */
final class Flip extends FilterAbstract
{
    public const NAME = 'flip';

    /**
     * @param string $axis possible values are: "x", "horizontal", "y", or "vertical"
     */
    public function __construct(private string $axis)
    {
    }

    public function getAxis(): string
    {
        return $this->axis;
    }
}
