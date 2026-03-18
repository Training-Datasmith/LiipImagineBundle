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

use Liip\ImagineBundle\Config\Filter\Argument\Point;

/**
 * @codeCoverageIgnore
 */
final class Paste extends FilterAbstract
{
    public const NAME = 'paste';

    public function __construct(private Point $start)
    {
    }

    public function getStart(): Point
    {
        return $this->start;
    }
}
