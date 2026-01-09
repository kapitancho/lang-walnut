<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistryCore as TypeRegistryCoreInterface;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\SealedType;

final class TypeRegistryCore implements TypeRegistryCoreInterface {
	public function __construct(private readonly TypeRegistry $typeRegistry) {}

	public DataType $cannotFormatString {
		get {
			return $this->typeRegistry->data(CoreType::CannotFormatString->typeName());
		}
	}
	public DataType $castNotAvailable {
		get {
			return $this->typeRegistry->data(CoreType::CastNotAvailable->typeName());
		}
	}
	public AliasType $cliEntryPoint {
		get {
			return $this->typeRegistry->alias(CoreType::CliEntryPoint->typeName());
		}
	}
	public AtomType $constructor {
		get {
			return $this->typeRegistry->atom(CoreType::Constructor->typeName());
		}
	}
	public AtomType $dependencyContainer {
		get {
			return $this->typeRegistry->atom(CoreType::DependencyContainer->typeName());
		}
	}
	public DataType $dependencyContainerError {
		get {
			return $this->typeRegistry->data(CoreType::DependencyContainerError->typeName());
		}
	}
	public EnumerationType $dependencyContainerErrorType {
		get {
			return $this->typeRegistry->enumeration(CoreType::DependencyContainerErrorType->typeName());
		}
	}
	public SealedType $externalError {
		get {
			return $this->typeRegistry->sealed(CoreType::ExternalError->typeName());
		}
	}
	public DataType $hydrationError {
		get {
			return $this->typeRegistry->data(CoreType::HydrationError->typeName());
		}
	}
	public DataType $indexOutOfRange {
		get {
			return $this->typeRegistry->data(CoreType::IndexOutOfRange->typeName());
		}
	}
	public OpenType $integerRange {
		get {
			return $this->typeRegistry->open(CoreType::IntegerRange->typeName());
		}
	}
	public DataType $integerNumberIntervalEndpoint {
		get {
			return $this->typeRegistry->data(CoreType::IntegerNumberIntervalEndpoint->typeName());
		}
	}
	public OpenType $integerNumberInterval {
		get {
			return $this->typeRegistry->open(CoreType::IntegerNumberInterval->typeName());
		}
	}
	public DataType $integerNumberRange {
		get {
			return $this->typeRegistry->data(CoreType::IntegerNumberRange->typeName());
		}
	}
	public DataType $invalidIntegerRange {
		get {
			return $this->typeRegistry->data(CoreType::InvalidIntegerRange->typeName());
		}
	}
	public DataType $invalidLengthRange {
		get {
			return $this->typeRegistry->data(CoreType::InvalidLengthRange->typeName());
		}
	}
	public DataType $invalidJsonString {
		get {
			return $this->typeRegistry->data(CoreType::InvalidJsonString->typeName());
		}
	}
	public DataType $invalidJsonValue {
		get {
			return $this->typeRegistry->data(CoreType::InvalidJsonValue->typeName());
		}
	}
	public DataType $invalidRealRange {
		get {
			return $this->typeRegistry->data(CoreType::InvalidRealRange->typeName());
		}
	}
	public DataType $invalidRegExp {
		get {
			return $this->typeRegistry->data(CoreType::InvalidRegExp->typeName());
		}
	}
	public DataType $invalidUuid {
		get {
			return $this->typeRegistry->data(CoreType::InvalidUuid->typeName());
		}
	}
	public DataType $invocationError {
		get {
			return $this->typeRegistry->data(CoreType::InvocationError->typeName());
		}
	}
	public AtomType $itemNotFound {
		get {
			return $this->typeRegistry->atom(CoreType::ItemNotFound->typeName());
		}
	}
	public AliasType $jsonValue {
		get {
			return $this->typeRegistry->alias(CoreType::JsonValue->typeName());
		}
	}
	public OpenType $lengthRange {
		get {
			return $this->typeRegistry->open(CoreType::LengthRange->typeName());
		}
	}
	public DataType $mapItemNotFound {
		get {
			return $this->typeRegistry->data(CoreType::MapItemNotFound->typeName());
		}
	}
	public AtomType $minusInfinity {
		get {
			return $this->typeRegistry->atom(CoreType::MinusInfinity->typeName());
		}
	}
	public AliasType $negativeInteger {
		get {
			return $this->typeRegistry->alias(CoreType::NegativeInteger->typeName());
		}
	}
	public AliasType $negativeReal {
		get {
			return $this->typeRegistry->alias(CoreType::NegativeReal->typeName());
		}
	}
	public AliasType $nonEmptyString {
		get {
			return $this->typeRegistry->alias(CoreType::NonEmptyString->typeName());
		}
	}
	public AliasType $nonNegativeInteger {
		get {
			return $this->typeRegistry->alias(CoreType::NonNegativeInteger->typeName());
		}
	}
	public AliasType $nonNegativeReal {
		get {
			return $this->typeRegistry->alias(CoreType::NonNegativeReal->typeName());
		}
	}
	public AliasType $nonPositiveInteger {
		get {
			return $this->typeRegistry->alias(CoreType::NonPositiveInteger->typeName());
		}
	}
	public AliasType $nonPositiveReal {
		get {
			return $this->typeRegistry->alias(CoreType::NonPositiveReal->typeName());
		}
	}
	public AtomType $noRegExpMatch {
		get {
			return $this->typeRegistry->atom(CoreType::NoRegExpMatch->typeName());
		}
	}
	public AliasType $nonZeroInteger {
		get {
			return $this->typeRegistry->alias(CoreType::NonZeroInteger->typeName());
		}
	}
	public AliasType $nonZeroReal {
		get {
			return $this->typeRegistry->alias(CoreType::NonZeroReal->typeName());
		}
	}
	public AtomType $notANumber {
		get {
			return $this->typeRegistry->atom(CoreType::NotANumber->typeName());
		}
	}
	public DataType $passwordString {
		get {
			return $this->typeRegistry->data(CoreType::PasswordString->typeName());
		}
	}
	public AtomType $plusInfinity {
		get {
			return $this->typeRegistry->atom(CoreType::PlusInfinity->typeName());
		}
	}
	public AliasType $positiveInteger {
		get {
			return $this->typeRegistry->alias(CoreType::PositiveInteger->typeName());
		}
	}
	public AliasType $positiveReal {
		get {
			return $this->typeRegistry->alias(CoreType::PositiveReal->typeName());
		}
	}
	public AtomType $random {
		get {
			return $this->typeRegistry->atom(CoreType::Random->typeName());
		}
	}
	public DataType $realNumberIntervalEndpoint {
		get {
			return $this->typeRegistry->data(CoreType::RealNumberIntervalEndpoint->typeName());
		}
	}
	public OpenType $realNumberInterval {
		get {
			return $this->typeRegistry->open(CoreType::RealNumberInterval->typeName());
		}
	}
	public DataType $realNumberRange {
		get {
			return $this->typeRegistry->data(CoreType::RealNumberRange->typeName());
		}
	}
	public OpenType $realRange {
		get {
			return $this->typeRegistry->open(CoreType::RealRange->typeName());
		}
	}
	public SealedType $regExp {
		get {
			return $this->typeRegistry->sealed(CoreType::RegExp->typeName());
		}
	}
	public DataType $regExpMatch {
		get {
			return $this->typeRegistry->data(CoreType::RegExpMatch->typeName());
		}
	}
	public AtomType $sliceNotInBytes {
		get {
			return $this->typeRegistry->atom(CoreType::SliceNotInBytes->typeName());
		}
	}
	public AtomType $substringNotInString {
		get {
			return $this->typeRegistry->atom(CoreType::SubstringNotInString->typeName());
		}
	}
	public DataType $unknownEnumerationValue {
		get {
			return $this->typeRegistry->data(CoreType::UnknownEnumerationValue->typeName());
		}
	}
	public OpenType $uuid {
		get {
			return $this->typeRegistry->open(CoreType::Uuid->typeName());
		}
	}
}