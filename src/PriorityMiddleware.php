<?php
/**
 * Class PriorityMiddleware
 *
 * @created      10.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace chillerlan\HTTP\Psr15;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use const PHP_INT_MIN;

/**
 * A middleware with a priority setting
 */
class PriorityMiddleware implements PriorityMiddlewareInterface{

	/**
	 * PriorityMiddleware constructor.
	 */
	public function __construct(
		protected MiddlewareInterface $middleware,
		protected int                 $priority = PHP_INT_MIN,
	){

	}

	/**
	 * @inheritDoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface{
		return $this->middleware->process($request, $handler);
	}

	/**
	 * @inheritDoc
	 */
	public function getPriority():int{
		return $this->priority;
	}

}
