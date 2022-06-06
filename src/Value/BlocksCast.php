<?php
namespace Kraenkvisuell\NovaCmsBlocks\Value;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Kraenkvisuell\NovaCmsBlocks\Concerns\HasBlocks;

class BlocksCast implements CastsAttributes
{
    use HasBlocks;

    /**
     * @var array
     */
    protected $layouts = [];

    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function get($model, string $key, $value, array $attributes)
    {
        $this->model = $model;

        return $this->cast($value, $this->getLayoutMapping());
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    protected function getLayoutMapping()
    {
        return $this->layouts;
    }
}
