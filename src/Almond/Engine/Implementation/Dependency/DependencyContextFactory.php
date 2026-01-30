<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Dependency;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext as DependencyContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContextFactory as DependencyContextFactoryInterface;

	final readonly class DependencyContextFactory implements DependencyContextFactoryInterface {
	public DependencyContextInterface $dependencyContext;
	public function __construct(DependencyContainer $dependencyContainer) {
		$this->dependencyContext = new DependencyContext($dependencyContainer, []);
	}
}