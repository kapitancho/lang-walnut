<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\NativeCodeHelper\ItemHelper;
use Walnut\Lang\Almond\Engine\Implementation\Type\Helper\BaseType;

final readonly class Item implements NativeMethod {
	use BaseType;
	private ItemHelper $itemHelper;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {
		$this->itemHelper = new ItemHelper(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry
		);
	}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if (
			$targetType instanceof IntersectionType ||
			$targetType instanceof RecordType ||
			$targetType instanceof MapType || (
				$targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record
			)
		) {
			return $this->itemHelper->validateMapItem($targetType, $parameterType, $origin);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RecordValue) {
			return $this->itemHelper->executeMapItem($target, $parameter);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}