<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext as DependencyContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContextFactory as DependencyContextFactoryInterface;

final readonly class DependencyContextFactory implements DependencyContextFactoryInterface {
	public DependencyContextInterface $dependencyContext;
	public function __construct(DependencyContainer $dependencyContainer) {
		$this->dependencyContext = new DependencyContext($dependencyContainer, []);
	}
}