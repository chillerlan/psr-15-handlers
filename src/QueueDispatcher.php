<?php
/**
 * Class QueueDispatcher
 *
 * @created      08.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace chillerlan\HTTP\Psr15;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use function array_pop;

/**
 * @see https://www.php-fig.org/psr/psr-15/meta/
 * @see https://mwop.net/blog/2018-01-23-psr-15.html
 * @see https://github.com/libreworks/caridea-dispatch
 */
class QueueDispatcher implements MiddlewareInterface, RequestHandlerInterface{

	/** @var \Psr\Http\Server\MiddlewareInterface[] */
	protected array $middlewareStack = [];

	/**
	 * QueueDispatcher constructor.
	 */
	public function __construct(
		protected RequestHandlerInterface $kernel,
		iterable|null                     $middlewareStack = null,
	){

		if(!empty($middlewareStack)){
			$this->addStack($middlewareStack);
		}

	}

	/**
	 * @throws \chillerlan\HTTP\Psr15\MiddlewareException
	 */
	public function addStack(iterable $middlewareStack):static{

		foreach($middlewareStack as $middleware){

			if(!$middleware instanceof MiddlewareInterface){
				throw new MiddlewareException('invalid middleware');
			}

			$this->middlewareStack[] = $middleware;
		}

		return $this;
	}

	/**
	 *
	 */
	public function add(MiddlewareInterface $middleware):static{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(ServerRequestInterface $request):ResponseInterface{
		return $this->getRunner($this->kernel)->handle($request);
	}

	/**
	 * @inheritDoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface{
		return $this->getRunner($handler)->handle($request);
	}

	/**
	 *
	 */
	protected function getRunner(RequestHandlerInterface $handler):RequestHandlerInterface{

		return new class ($handler, $this->middlewareStack) implements RequestHandlerInterface{

			public function __construct(
				protected RequestHandlerInterface $kernel,
				protected array                   $middlewareStack,
			){
			}

			public function handle(ServerRequestInterface $request):ResponseInterface{

				if(empty($this->middlewareStack)){
					return $this->kernel->handle($request);
				}

				$middleware = array_pop($this->middlewareStack);

				return $middleware->process($request, $this);
			}
		};

	}

}
