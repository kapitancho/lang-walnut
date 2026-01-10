<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistryCore as ValueRegistryCoreInterface;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;

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
		return $this->valueRegistry->dataValue(CoreType::CannotFormatString->typeName(), $value);
	}

	public function castNotAvailable(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::CastNotAvailable->typeName(), $value);
	}

	public function dependencyContainerError(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::DependencyContainerError->typeName(), $value);
	}

	public function dependencyContainerErrorType(EnumValueIdentifier $value): EnumerationValue {
		return $this->valueRegistry->enumerationValue(CoreType::DependencyContainerErrorType->typeName(), $value);
	}

	public function externalError(RecordValue $value): SealedValue {
		return $this->valueRegistry->sealedValue(CoreType::ExternalError->typeName(), $value);
	}

	public function hydrationError(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::HydrationError->typeName(), $value);
	}

	public function indexOutOfRange(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::IndexOutOfRange->typeName(), $value);
	}

	public function integerNumberIntervalEndpoint(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::IntegerNumberIntervalEndpoint->typeName(), $value);
	}
	public function integerNumberInterval(RecordValue $value): OpenValue {
		return $this->valueRegistry->openValue(CoreType::IntegerNumberInterval->typeName(), $value);
	}
	public function integerNumberRange(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::IntegerNumberRange->typeName(), $value);
	}

	public function invalidIntegerRange(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidIntegerRange->typeName(), $value);
	}

	public function invalidJsonString(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidJsonString->typeName(), $value);
	}

	public function invalidJsonValue(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidJsonValue->typeName(), $value);
	}

	public function invalidRealRange(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidRealRange->typeName(), $value);
	}

	public function invalidRegExp(StringValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidRegExp->typeName(), $this->valueRegistry->record(['expression' => $value]));
	}

	public function invalidUuid(StringValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvalidUuid->typeName(), $this->valueRegistry->record(['value' => $value]));
	}

	public function invocationError(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::InvocationError->typeName(), $value);
	}

	public function mapItemNotFound(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::MapItemNotFound->typeName(), $value);
	}

	public function realNumberIntervalEndpoint(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::RealNumberIntervalEndpoint->typeName(), $value);
	}
	public function realNumberInterval(RecordValue $value): OpenValue {
		return $this->valueRegistry->openValue(CoreType::RealNumberInterval->typeName(), $value);
	}
	public function realNumberRange(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::RealNumberRange->typeName(), $value);
	}


	public function regExpMatch(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::RegExpMatch->typeName(), $value);
	}

	public function unknownEnumerationValue(RecordValue $value): DataValue {
		return $this->valueRegistry->dataValue(CoreType::UnknownEnumerationValue->typeName(), $value);
	}

	public function uuid(StringValue $value): OpenValue {
		return $this->valueRegistry->openValue(CoreType::Uuid->typeName(), $value);
	}
}