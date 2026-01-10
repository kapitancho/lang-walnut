<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;

interface ValueRegistryCore {
	public function cannotFormatString(RecordValue $value): DataValue;
	public function castNotAvailable(RecordValue $value): DataValue;
	public AtomValue        $constructor                   { get; }
	public AtomValue        $dependencyContainer           { get; }
	public function dependencyContainerError(RecordValue $value): DataValue;
	public function dependencyContainerErrorType(EnumValueIdentifier $value): EnumerationValue;
	//public EnumerationValue $dependencyContainerErrorType { get; }
	public function externalError(RecordValue $value): SealedValue;
	public function hydrationError(RecordValue $value): DataValue;
	public function indexOutOfRange(RecordValue $value): DataValue;
	//public OpenValue        $integerRange                  { get; }
	public function integerNumberIntervalEndpoint(RecordValue $value): DataValue;
	public function integerNumberInterval(RecordValue $value): OpenValue;
	public function integerNumberRange(RecordValue $value): DataValue;
	public function invalidIntegerRange(RecordValue $value): DataValue;
	//public DataValue        $invalidLengthRange            { get; }
	public function invalidJsonString(RecordValue $value): DataValue;
	public function invalidJsonValue(RecordValue $value): DataValue;
	public function invalidRealRange(RecordValue $value): DataValue;
	public function invalidRegExp(StringValue $value): DataValue;
	public function invalidUuid(StringValue $value): DataValue;
	public function invocationError(RecordValue $value): DataValue;
	public AtomValue        $itemNotFound                  { get; }
	//public OpenValue        $lengthRange                   { get; }
	public function mapItemNotFound(RecordValue $value): DataValue;
	public AtomValue        $minusInfinity                 { get; }
	public AtomValue        $noRegExpMatch                { get; }
	public AtomValue        $notANumber                    { get; }
	//public DataValue        $passwordString                { get; }
	public AtomValue        $plusInfinity                  { get; }
	public AtomValue        $random                        { get; }
	public function realNumberIntervalEndpoint(RecordValue $value): DataValue;
	public function realNumberInterval(RecordValue $value): OpenValue;
	public function realNumberRange(RecordValue $value): DataValue;
	//public OpenValue        $realRange                     { get; }
	//public SealedValue      $regExp                        { get; }
	public function regExpMatch(RecordValue $value): DataValue;
	public AtomValue        $sliceNotInBytes               { get; }
	public AtomValue        $substringNotInString          { get; }
	public function unknownEnumerationValue(RecordValue $value): DataValue;
	public function uuid(StringValue $value): OpenValue;
}