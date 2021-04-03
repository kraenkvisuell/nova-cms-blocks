<?php

namespace Kraenkvisuell\NovaCmsBlocks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kraenkvisuell\NovaCmsBlocks\Http\ParsesBlocksAttributes;
use Kraenkvisuell\NovaCmsBlocks\Http\TransformsBlocksErrors;
use Kraenkvisuell\NovaCmsBlocks\Http\BlocksAttribute;

class InterceptBlocksAttributes
{
    use ParsesBlocksAttributes;
    use TransformsBlocksErrors;

    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        if (!$this->requestHasParsableBlocksInputs($request)) {
            return $next($request);
        }

        $request->merge($this->getParsedBlocksInputs($request));
        $request->request->remove(BlocksAttribute::REGISTER);

        $response = $next($request);

        if (!$this->shouldTransformBlocksErrors($response)) {
            return $response;
        }

        return $this->transformBlocksErrors($response);
    }
}
