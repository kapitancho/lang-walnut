<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistryCore as ValueRegistryCoreInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

final class ValueRegistryCore implements ValueRegistryCoreInterface {
	public function __construct(private readonly ValueRegistry $valueRegistry) { }

	public AtomValue $constructor {
		get {
			return $this->valueRegistry->atom(CoreType::Constructor->typeName());
		}
	}
	public AtomValue $dependencyContainer {
		get {
			return $this->valueRegistry->atom(CoreType::DependencyContainer->typeName());
		}
	}
	public AtomValue $invalidString {
		get {
			return $this->valueRegistry->atom(CoreType::InvalidString->typeName());
		}
	}
	public AtomValue $itemNotFound {
		get {
			return $this->valueRegistry->atom(CoreType::ItemNotFound->typeName());
		}
	}
	public AtomValue $minusInfinity {
		get {
			return $this->valueRegistry->atom(CoreType::MinusInfinity->typeName());
		}
	}
	public AtomValue $noRegExpMatch {
		get {
			return $this->valueRegistry->atom(CoreType::NoRegExpMatch->typeName());
		}
	}
	public AtomValue $notANumber {
		get {
			return $this->valueRegistry->atom(CoreType::NotANumber->typeName());
		}
	}
	public AtomValue $plusInfinity {
		get {
			return $this->valueRegistry->atom(CoreType::PlusInfinity->typeName());
		}
	}
	public AtomValue $random {
		get {
			return $this->valueRegistry->atom(CoreType::Random->typeName());
		}
	}
	public AtomValue $sliceNotInBytes {
		get {
			return $this->valueRegistry->atom(CoreType::SliceNotInBytes->typeName());
		}
	}
	public AtomValue $substringNotInString {
		get {
			return $this->valueRegistry->atom(CoreType::SubstringNotInString->typeName());
		}
	}

	public function cannotFormatString(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::CannotFormatString->typeName(), $value);
	}

	public function castNotAvailable(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::CastNotAvailable->typeName(), $value);
	}

	public function dependencyContainerError(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::DependencyContainerError->typeName(), $value);
	}

	public function dependencyContainerErrorType(EnumerationValueName $value): EnumerationValue {
		return $this->valueRegistry->enumeration(CoreType::DependencyContainerErrorType->typeName(), $value);
	}

	public function externalError(RecordValue $value): SealedValue {
		return $this->valueRegistry->sealed(CoreType::ExternalError->typeName(), $value);
	}

	public function hydrationError(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::HydrationError->typeName(), $value);
	}

	public function indexOutOfRange(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::IndexOutOfRange->typeName(), $value);
	}

	public function integerNumberIntervalEndpoint(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::IntegerNumberIntervalEndpoint->typeName(), $value);
	}
	public function integerNumberInterval(RecordValue $value): OpenValue {
		return $this->valueRegistry->open(CoreType::IntegerNumberInterval->typeName(), $value);
	}
	public function integerNumberRange(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::IntegerNumberRange->typeName(), $value);
	}

	public function invalidIntegerRange(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidIntegerRange->typeName(), $value);
	}

	public function invalidJsonString(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidJsonString->typeName(), $value);
	}

	public function invalidJsonValue(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidJsonValue->typeName(), $value);
	}

	public function invalidRealRange(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidRealRange->typeName(), $value);
	}

	public function invalidRegExp(StringValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidRegExp->typeName(), $this->valueRegistry->record(['expression' => $value]));
	}

	public function invalidUuid(StringValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvalidUuid->typeName(), $this->valueRegistry->record(['value' => $value]));
	}

	public function invocationError(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::InvocationError->typeName(), $value);
	}

	public function mapItemNotFound(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::MapItemNotFound->typeName(), $value);
	}

	public function realNumberIntervalEndpoint(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::RealNumberIntervalEndpoint->typeName(), $value);
	}
	public function realNumberInterval(RecordValue $value): OpenValue {
		return $this->valueRegistry->open(CoreType::RealNumberInterval->typeName(), $value);
	}
	public function realNumberRange(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::RealNumberRange->typeName(), $value);
	}


	public function regExpMatch(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::RegExpMatch->typeName(), $value);
	}

	public function unknownEnumerationValue(RecordValue $value): DataValue {
		return $this->valueRegistry->data(CoreType::UnknownEnumerationValue->typeName(), $value);
	}

	public function uuid(StringValue $value): OpenValue {
		return $this->valueRegistry->open(CoreType::Uuid->typeName(), $value);
	}
}