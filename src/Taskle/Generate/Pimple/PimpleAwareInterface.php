<?php
namespace Taskle\Generate\Pimple;

use Pimple\Container;

interface PimpleAwareInterface
{
    /**
     * Sets the Container.
     *
     * @param Container|null $container A Container instance or null
     *
     * @api
     */
    public function setContainer(Container $container = null);
}
