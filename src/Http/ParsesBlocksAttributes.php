<?php

namespace Kraenkvisuell\NovaCmsBlocks\Http;

use Illuminate\Http\Request;
use Kraenkvisuell\NovaCmsBlocks\Http\BlocksAttribute;

trait ParsesBlocksAttributes
{
    /**
     * The registered flexible field attributes
     *
     * @var string
     */
    protected $registered = [];

    /**
     * Check if given request should be handled by the middleware
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function requestHasParsableBlocksInputs(Request $request)
    {
        return (in_array($request->method(), ['POST','PUT']) &&
                is_string($request->input(BlocksAttribute::REGISTER)));
    }

    /**
     * Transform the request's flexible values
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getParsedBlocksInputs(Request $request)
    {
        $this->registerBlocksFields($request->input(BlocksAttribute::REGISTER));

        return array_reduce(array_keys($request->all()), function($carry, $attribute) use ($request) {
            $value = $request->input($attribute);

            if(!$this->isBlocksAttribute($attribute, $value)) return $carry;

            $carry[$attribute] = $this->getParsedBlocksValue($value);

            return $carry;
        }, []);
    }

    /**
     * Apply JSON decode and recursively check for nested values
     *
     * @param  mixed $value
     * @return array
     */
    protected function getParsedBlocksValue($value)
    {
        if(is_string($value)) {
            $raw = json_decode($value, true);
        } else {
            $raw = $value;
        }

        if(!is_array($raw)) return $value;

        return array_map(function($group) {
            return $this->getParsedBlocksGroup($group);
        }, $raw);
    }

    /**
     * Cleans & prepares a filled group
     *
     * @param  array $group
     * @return array
     */
    protected function getParsedBlocksGroup($group)
    {
        $clean = [
            'layout' => $group['layout'] ?? null,
            'key' => $group['key'] ?? null,
            'attributes' => [],
        ];

        foreach ($group['attributes'] ?? [] as $attribute => $value) {
            $this->fillBlocksAttributes($clean['attributes'], $clean['key'], $attribute, $value);
        }

        foreach ($clean['attributes'] as $attribute => $value) {
            if(!$this->isBlocksAttribute($attribute, $value)) continue;
            $clean['attributes'][$attribute] = $this->getParsedBlocksValue($value);
        }

        return $clean;
    }

    /**
     * Fill a flexible group's attributes with cleaned attributes & values
     *
     * @param  array $attributes
     * @param  string $group
     * @param  string $attribute
     * @param  string $value
     * @return void
     */
    protected function fillBlocksAttributes(&$attributes, $group, $attribute, $value)
    {
        $attribute = $this->parseAttribute($attribute, $group);

        if($attribute->isBlocksFieldsRegister()) {
            $this->registerBlocksFields($value, $group);
            return;
        }

        $attribute->setDataIn($attributes, $value);
    }

    /**
     * Analyse and clean up the raw attribute
     *
     * @param  string  $attribute
     * @param  string  $group
     * @return \Kraenkvisuell\NovaCmsBlocks\Http\BlocksAttribute
     */
    protected function parseAttribute($attribute, $group)
    {
        return new BlocksAttribute($attribute, $group);
    }

    /**
     * Add flexible attributes to the register
     *
     * @param  null|string $value
     * @param  null|string $group
     * @return void
     */
    protected function registerBlocksFields($value, $group = null)
    {
        if(!$value) {
            return;
        }

        if(!is_array($value)) {
            $value = json_decode($value);
        }

        foreach ($value as $attribute) {
            $this->registerBlocksField($attribute, $group);
        }
    }

    /**
     * Add an attribute to the register
     *
     * @param  mixed $attribute
     * @param  null|string $group
     * @return void
     */
    protected function registerBlocksField($attribute, $group = null)
    {
        $attribute = $this->parseAttribute(strval($attribute), $group);

        $this->registered[] = $attribute;
    }

    /**
     * Check if given attribute is a registered and usable 
     * flexible attribute
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    protected function isBlocksAttribute($attribute, $value)
    {
        if(!$this->getBlocksAttribute($attribute)) {
            return false;
        }
        
        if(!$value || !is_string($value)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve a registered flexible attribute
     *
     * @param  string $attribute
     * @return \Kraenkvisuell\NovaCmsBlocks\Http\BlocksAttribute
     */
    protected function getBlocksAttribute($attribute)
    {
        foreach ($this->registered as $registered) {
            if($registered->name === $attribute) return $registered;
        }
    }
}
