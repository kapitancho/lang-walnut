<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistryCore as TypeRegistryCoreInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;

final class TypeRegistryCore implements TypeRegistryCoreInterface {

	public function __construct(private readonly UserlandTypeRegistry $registry) {}

	public DataType $cannotFormatString {
		get { return $this->registry->data(CoreType::CannotFormatString->typeName()); }
	}
	public DataType $castNotAvailable {
		get { return $this->registry->data(CoreType::CastNotAvailable->typeName()); }
	}
	public AliasType $cliEntryPoint {
		get { return $this->registry->alias(CoreType::CliEntryPoint->typeName()); }
	}
	public AtomType $constructor {
		get { return $this->registry->atom(CoreType::Constructor->typeName()); }
	}
	public AtomType $dependencyContainer {
		get { return $this->registry->atom(CoreType::DependencyContainer->typeName()); }
	}
	public DataType $dependencyContainerError {
		get { return $this->registry->data(CoreType::DependencyContainerError->typeName()); }
	}
	public EnumerationType $dependencyContainerErrorType {
		get { return $this->registry->enumeration(CoreType::DependencyContainerErrorType->typeName()); }
	}
	public SealedType $externalError {
		get { return $this->registry->sealed(CoreType::ExternalError->typeName()); }
	}
	public DataType $hydrationError {
		get { return $this->registry->data(CoreType::HydrationError->typeName()); }
	}
	public DataType $indexOutOfRange {
		get { return $this->registry->data(CoreType::IndexOutOfRange->typeName()); }
	}
	public OpenType $integerRange {
		get { return $this->registry->open(CoreType::IntegerRange->typeName()); }
	}
	public DataType $integerNumberIntervalEndpoint {
		get { return $this->registry->data(CoreType::IntegerNumberIntervalEndpoint->typeName()); }
	}
	public OpenType $integerNumberInterval {
		get { return $this->registry->open(CoreType::IntegerNumberInterval->typeName()); }
	}
	public DataType $integerNumberRange {
		get { return $this->registry->data(CoreType::IntegerNumberRange->typeName()); }
	}
	public DataType $invalidIntegerRange {
		get { return $this->registry->data(CoreType::InvalidIntegerRange->typeName()); }
	}
	public DataType $invalidLengthRange {
		get { return $this->registry->data(CoreType::InvalidLengthRange->typeName()); }
	}
	public DataType $invalidJsonString {
		get { return $this->registry->data(CoreType::InvalidJsonString->typeName()); }
	}
	public DataType $invalidJsonValue {
		get { return $this->registry->data(CoreType::InvalidJsonValue->typeName()); }
	}
	public DataType $invalidRealRange {
		get { return $this->registry->data(CoreType::InvalidRealRange->typeName()); }
	}
	public DataType $invalidRegExp {
		get { return $this->registry->data(CoreType::InvalidRegExp->typeName()); }
	}
	public DataType $invalidUuid {
		get { return $this->registry->data(CoreType::InvalidUuid->typeName()); }
	}
	public DataType $invocationError {
		get { return $this->registry->data(CoreType::InvocationError->typeName()); }
	}
	public AtomType $itemNotFound {
		get { return $this->registry->atom(CoreType::ItemNotFound->typeName()); }
	}
	public AliasType $jsonValue {
		get { return $this->registry->alias(CoreType::JsonValue->typeName()); }
	}
	public OpenType $lengthRange {
		get { return $this->registry->open(CoreType::LengthRange->typeName()); }
	}
	public DataType $mapItemNotFound {
		get { return $this->registry->data(CoreType::MapItemNotFound->typeName()); }
	}
	public AtomType $minusInfinity {
		get { return $this->registry->atom(CoreType::MinusInfinity->typeName()); }
	}
	public AliasType $negativeInteger {
		get { return $this->registry->alias(CoreType::NegativeInteger->typeName()); }
	}
	public AliasType $negativeReal {
		get { return $this->registry->alias(CoreType::NegativeReal->typeName()); }
	}
	public AliasType $nonEmptyString {
		get { return $this->registry->alias(CoreType::NonEmptyString->typeName()); }
	}
	public AliasType $nonNegativeInteger {
		get { return $this->registry->alias(CoreType::NonNegativeInteger->typeName()); }
	}
	public AliasType $nonNegativeReal {
		get { return $this->registry->alias(CoreType::NonNegativeReal->typeName()); }
	}
	public AliasType $nonPositiveInteger {
		get { return $this->registry->alias(CoreType::NonPositiveInteger->typeName()); }
	}
	public AliasType $nonPositiveReal {
		get { return $this->registry->alias(CoreType::NonPositiveReal->typeName()); }
	}
	public AtomType $noRegExpMatch {
		get { return $this->registry->atom(CoreType::NoRegExpMatch->typeName()); }
	}
	public AliasType $nonZeroInteger {
		get { return $this->registry->alias(CoreType::NonZeroInteger->typeName()); }
	}
	public AliasType $nonZeroReal {
		get { return $this->registry->alias(CoreType::NonZeroReal->typeName()); }
	}
	public AtomType $notANumber {
		get { return $this->registry->atom(CoreType::NotANumber->typeName()); }
	}
	public DataType $passwordString {
		get { return $this->registry->data(CoreType::PasswordString->typeName()); }
	}
	public AtomType $plusInfinity {
		get { return $this->registry->atom(CoreType::PlusInfinity->typeName()); }
	}
	public AliasType $positiveInteger {
		get { return $this->registry->alias(CoreType::PositiveInteger->typeName()); }
	}
	public AliasType $positiveReal {
		get { return $this->registry->alias(CoreType::PositiveReal->typeName()); }
	}
	public AtomType $random {
		get { return $this->registry->atom(CoreType::Random->typeName()); }
	}
	public DataType $realNumberIntervalEndpoint {
		get { return $this->registry->data(CoreType::RealNumberIntervalEndpoint->typeName()); }
	}
	public OpenType $realNumberInterval {
		get { return $this->registry->open(CoreType::RealNumberInterval->typeName()); }
	}
	public DataType $realNumberRange {
		get { return $this->registry->data(CoreType::RealNumberRange->typeName()); }
	}
	public OpenType $realRange {
		get { return $this->registry->open(CoreType::RealRange->typeName()); }
	}
	public SealedType $regExp {
		get { return $this->registry->sealed(CoreType::RegExp->typeName()); }
	}
	public DataType $regExpMatch {
		get { return $this->registry->data(CoreType::RegExpMatch->typeName()); }
	}
	public AtomType $sliceNotInBytes {
		get { return $this->registry->atom(CoreType::SliceNotInBytes->typeName()); }
	}
	public AtomType $substringNotInString {
		get { return $this->registry->atom(CoreType::SubstringNotInString->typeName()); }
	}
	public DataType $unknownEnumerationValue {
		get { return $this->registry->data(CoreType::UnknownEnumerationValue->typeName()); }
	}
	public OpenType $uuid {
		get { return $this->registry->open(CoreType::Uuid->typeName()); }
	}

}