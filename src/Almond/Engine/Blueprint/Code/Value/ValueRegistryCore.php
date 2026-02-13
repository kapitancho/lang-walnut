<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;

interface ValueRegistryCore {
	public function cannotFormatString(RecordValue $value): DataValue;
	public function castNotAvailable(RecordValue $value): DataValue;
	public AtomValue        $constructor                   { get; }
	public AtomValue        $dependencyContainer           { get; }
	public function dependencyContainerError(RecordValue $value): DataValue;
	public function dependencyContainerErrorType(EnumerationValueName $value): EnumerationValue;
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
	public AtomValue        $invalidString                   { get; }
	public AtomValue        $itemNotFound                    { get; }
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