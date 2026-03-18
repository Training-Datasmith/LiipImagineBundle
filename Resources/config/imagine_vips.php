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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Vips\Imagine;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('liip_imagine.vips', Imagine::class)
        ->private()
        ->call('setMetadataReader', [service('liip_imagine.meta_data.reader')]);
};
