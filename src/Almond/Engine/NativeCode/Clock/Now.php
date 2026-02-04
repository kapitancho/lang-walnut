<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Clock;

use DateTimeImmutable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Now implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(
		Type $targetType,
		Type $parameterType,
		Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof AtomType && $targetType->name->equals(
			new TypeName('Clock')
		)) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->typeByName(new TypeName('DateAndTime'))
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function execute(
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof AtomValue && $target->type->name->equals(
			new TypeName('Clock')
		)) {
			$now = new DateTimeImmutable;
			return $this->valueRegistry->open(
				new TypeName('DateAndTime'),
				$this->valueRegistry->record([
					'date' => $this->valueRegistry->open(
						new TypeName('Date'),
						$this->valueRegistry->record([
							'year' => $this->valueRegistry->integer((int)$now->format('Y')),
							'month' => $this->valueRegistry->integer((int)$now->format('m')),
							'day' => $this->valueRegistry->integer((int)$now->format('d')),
						])
					),
					'time' => $this->valueRegistry->open(
						new TypeName('Time'),
						$this->valueRegistry->record([
							'hour' => $this->valueRegistry->integer((int)$now->format('H')),
							'minute' => $this->valueRegistry->integer((int)$now->format('i')),
							'second' => $this->valueRegistry->integer((int)$now->format('s')),
						])
					),
				])
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}