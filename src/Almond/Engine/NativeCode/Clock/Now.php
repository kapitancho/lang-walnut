<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Clock;

use DateTimeImmutable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

final readonly class Now extends NamedNativeMethod {

	protected function isNamedTypeValid(NamedType $namedType, mixed $origin): bool {
		return $namedType->name->equals(new TypeName('Clock'));
	}

	protected function getValidator(): callable {
		return fn(AtomType $targetType, Type $parameterType, mixed $origin): Type =>
			$this->typeRegistry->typeByName(new TypeName('DateAndTime'));
	}

	protected function getExecutor(): callable {
		return function(AtomValue $target, NullValue $parameter): Value {
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
		};
	}
}