<?php

namespace Walnut\Lang\Implementation\Program;

use Walnut\Lang\Blueprint\Program\ProgramContextFactory as ProgramContextFactoryInterface;
use Walnut\Lang\Implementation\AST\Parser\BytesEscapeCharHandler;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\ComplexTypeBuilder;
use Walnut\Lang\Implementation\Program\Builder\ComplexTypeStorage;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\MethodAnalyser;
use Walnut\Lang\Implementation\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class ProgramContextFactory implements ProgramContextFactoryInterface {
	private const string lookupNamespace = 'Walnut\\Lang\\NativeCode';

	public ProgramContext $programContext {
		get => new ProgramContext(
			$customMethodRegistryBuilder = new CustomMethodRegistryBuilder(),
			$customMethodRegistryBuilder,
			$typeRegistry = new TypeRegistry(
				$methodFinder = new MainMethodRegistry(
					$nativeCodeTypeMapper = new NativeCodeTypeMapper(),
					$customMethodRegistryBuilder,
					[
						self::lookupNamespace
					]
				),
				$ech = new StringEscapeCharHandler(),
				$complexTypeStorage = new ComplexTypeStorage()
			),
			new TypeRegistryBuilder(
				$typeRegistry,
				$customMethodRegistryBuilder,
				new ComplexTypeBuilder(),
				$complexTypeStorage
			),
			$valueRegistry = new ValueRegistry(
				$typeRegistry,
				$ech,
				new BytesEscapeCharHandler()
			),
			new ExpressionRegistry($typeRegistry, $valueRegistry),
			$methodFinder,
			new MethodAnalyser(
				$typeRegistry,
				$methodFinder
			),
			VariableValueScope::empty(),
			$nativeCodeTypeMapper
		);
	}
}