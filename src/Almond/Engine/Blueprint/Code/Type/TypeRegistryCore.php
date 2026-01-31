<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;

interface TypeRegistryCore {
	public DataType        $cannotFormatString            { get; }
	public DataType        $castNotAvailable              { get; }
	public AliasType       $cliEntryPoint                 { get; }
	public AtomType        $dependencyContainer           { get; }
	public DataType        $dependencyContainerError      { get; }
	public EnumerationType $dependencyContainerErrorType  { get; }
	public SealedType      $externalError                 { get; }
	public DataType        $hydrationError                { get; }
	public DataType        $indexOutOfRange               { get; }
	public OpenType        $integerRange                  { get; }
	public DataType        $integerNumberIntervalEndpoint { get; }
	public OpenType        $integerNumberInterval         { get; }
	public DataType        $integerNumberRange            { get; }
	public DataType        $invalidIntegerRange           { get; }
	public DataType        $invalidLengthRange            { get; }
	public DataType        $invalidJsonString             { get; }
	public DataType        $invalidJsonValue              { get; }
	public DataType        $invalidRealRange              { get; }
	public DataType        $invalidRegExp                 { get; }
	public DataType        $invalidUuid                   { get; }
	public DataType        $invocationError               { get; }
	public AtomType        $itemNotFound                  { get; }
	public AliasType       $jsonValue                     { get; }
	public OpenType        $lengthRange                   { get; }
	public DataType        $mapItemNotFound               { get; }
	public AtomType        $minusInfinity                 { get; }
	public AliasType       $negativeInteger               { get; }
	public AliasType       $negativeReal                  { get; }
	public AliasType       $nonEmptyString                { get; }
	public AliasType       $nonNegativeInteger            { get; }
	public AliasType       $nonNegativeReal               { get; }
	public AliasType       $nonPositiveInteger            { get; }
	public AliasType       $nonPositiveReal               { get; }
	public AtomType        $noRegExpMatch                { get; }
	public AliasType       $nonZeroInteger                { get; }
	public AliasType       $nonZeroReal                   { get; }
	public AtomType        $notANumber                    { get; }
	public DataType        $passwordString                { get; }
	public AtomType        $plusInfinity                  { get; }
	public AliasType       $positiveInteger               { get; }
	public AliasType       $positiveReal                  { get; }
	public AtomType        $random                        { get; }
	public DataType        $realNumberIntervalEndpoint    { get; }
	public OpenType        $realNumberInterval            { get; }
	public DataType        $realNumberRange               { get; }
	public OpenType        $realRange                     { get; }
	public SealedType      $regExp                        { get; }
	public DataType        $regExpMatch                   { get; }
	public AtomType        $sliceNotInBytes               { get; }
	public AtomType        $substringNotInString          { get; }
	public DataType        $unknownEnumerationValue       { get; }
	public OpenType        $uuid                          { get; }
}