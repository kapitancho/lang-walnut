<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder as ProgramBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyError;
use Walnut\Lang\Blueprint\Program\DependencyContainer\UnresolvableDependency;
use Walnut\Lang\Blueprint\Program\Factory\ProgramFactory as ProgramFactoryInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Program\Factory\ProgramFactory;
use Walnut\Lang\Implementation\Program\Type\UnionTypeNormalizer;
use Walnut\Lang\Implementation\Type\UnionType;

final readonly class EmptyDependencyContainer implements DependencyContainer {
	public function valueByType(Type $type): Value|DependencyError {
		return new DependencyError(UnresolvableDependency::notFound, $type);
	}
}