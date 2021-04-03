<?php

namespace Kraenkvisuell\NovaCmsBlocks\Layouts;

use Kraenkvisuell\NovaCmsBlocks\Blocks;

abstract class Preset
{
    /**
     * Execute the preset configuration
     *
     * @return void
     */
    abstract public function handle(Blocks $field);

}
