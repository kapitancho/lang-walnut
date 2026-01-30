<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\ComplexTypeHydrator;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\CompositeTypeHydrator;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\HydrationException;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\Hydrator as HydratorInterface;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\SimpleTypeHydrator;
use Walnut\Lang\Blueprint\Code\NativeCode\Hydrator\TypeTypeHydrator;
use Walnut\Lang\Blueprint\Type\ComplexType;
use Walnut\Lang\Blueprint\Type\CompositeType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\SimpleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class Hydrator implements HydratorInterface {

	public function __construct(
		private SimpleTypeHydrator    $simpleTypeHydrator,
		private CompositeTypeHydrator $compositeTypeHydrator,
		private ComplexTypeHydrator   $complexTypeHydrator,
		private TypeTypeHydrator      $typeTypeHydrator,
	) {}

	/** @throws HydrationException */
	public function hydrate(Value $value, Type $targetType, string $hydrationPath): Value {
		return match(true) {
			$targetType instanceof SimpleType =>
				$this->simpleTypeHydrator->hydrate($value, $targetType, $hydrationPath),
			$targetType instanceof CompositeType =>
				$this->compositeTypeHydrator->hydrate($this, $value, $targetType, $hydrationPath),
			$targetType instanceof TypeType =>
				$this->typeTypeHydrator->hydrate($value, $targetType, $hydrationPath),
			$targetType instanceof ComplexType =>
				$this->complexTypeHydrator->hydrate($this, $value, $targetType, $hydrationPath),
			$targetType instanceof MetaType => throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf(
					"Values of cannot be hydrated to type %s.",
					$targetType->value->value
				)
			),

			// @codeCoverageIgnoreStart
			default => throw new HydrationException(
				$value,
				$hydrationPath,
				"Unsupported type: " . $targetType::class
			)
			// @codeCoverageIgnoreEnd
		};
	}

}