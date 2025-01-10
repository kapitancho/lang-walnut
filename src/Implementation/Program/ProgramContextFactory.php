<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Program\ProgramContextFactory as ProgramContextFactoryInterface;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class ProgramContextFactory implements ProgramContextFactoryInterface {
	private const string lookupNamespace = 'Walnut\\Lang\\NativeCode';

	public ProgramContext $programContext {
		get => new ProgramContext(
			$customMethodRegistryBuilder = new CustomMethodRegistryBuilder(),
			$customMethodRegistryBuilder,
			$typeRegistryBuilder = new TypeRegistryBuilder(
				$customMethodRegistryBuilder
			),
			$typeRegistryBuilder,
			$valueRegistry = new ValueRegistry($typeRegistryBuilder),
			new ExpressionRegistry($typeRegistryBuilder, $valueRegistry),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				$customMethodRegistryBuilder,
				[
					self::lookupNamespace
				]
			),
			new ScopeBuilder(VariableValueScope::empty())
		);
	}
}