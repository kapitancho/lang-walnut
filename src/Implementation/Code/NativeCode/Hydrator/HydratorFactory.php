<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\HydratorFactory as HydratorFactoryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final readonly class HydratorFactory implements HydratorFactoryInterface {

	public Hydrator $hydrator;
	public SimpleTypeHydrator $simpleTypeHydrator;
	public CompositeTypeHydrator $compositeTypeHydrator;
	public ComplexTypeHydrator $complexTypeHydrator;
	public TypeTypeHydrator $typeTypeHydrator;

	public function __construct(
		TypeRegistry $typeRegistry,
		ValueRegistry $valueRegistry,
		MethodContext $methodContext,
	) {
		$this->simpleTypeHydrator = new SimpleTypeHydrator($valueRegistry);
		$this->compositeTypeHydrator = new CompositeTypeHydrator($valueRegistry);
		$this->complexTypeHydrator = new ComplexTypeHydrator(
			$typeRegistry,
			$valueRegistry,
			$methodContext,
		);
		$this->typeTypeHydrator = new TypeTypeHydrator($typeRegistry, $valueRegistry);
		$this->hydrator = new Hydrator(
			$this->simpleTypeHydrator,
			$this->compositeTypeHydrator,
			$this->complexTypeHydrator,
			$this->typeTypeHydrator,
		);
	}

}