<?php

namespace Walnut\Lang\Almond\Runner\Implementation;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleContentProvider;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class ModuleContentProviderAdapter implements ModuleContentProvider {

	public function __construct(private ModuleLookupContext $lookupContext) {}

	public function contentOf(string $moduleName): string|null {
		return $this->lookupContext->sourceOf($moduleName);
	}
}