<?php

namespace Walnut\Lang;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class ToStringTest extends BaseProgramTestHelper {

	public function testValues(): void {
		$this->addSampleTypes();
		$i = fn(string $name) => new TypeNameIdentifier($name);
		$ev = fn(string $name) => new EnumValueIdentifier($name);
		$vr = $this->valueRegistry;
		foreach([
			'MyAtom[]' => $vr->atom($i('MyAtom')),
			'MyEnum.A' => $vr->enumerationValue($i('MyEnum'), $ev('A')),
			'MySealed[:]' => $vr->sealedValue($i('MySealed'), $vr->record([])),
			'MySubtype{null}' => $vr->subtypeValue($i('MySubtype'), $vr->null),
			'true' => $vr->boolean(true),
			'false' => $vr->boolean(false),
			'null' => $vr->null,
			'123' => $vr->integer(123),
			'123.456' => $vr->real(123.456),
			"'abc'" => $vr->string('abc'),
			'[]' => $vr->tuple([]),
			"[1, 'abc']" => $vr->tuple([$vr->integer(1), $vr->string('abc')]),
			'[:]' => $vr->record([]),
	        "[a: 1, b: 'abc']" => $vr->record(['a' => $vr->integer(1), 'b' => $vr->string('abc')]),
			'[;]' => $vr->set([]),
	        "[1;]" => $vr->set([$vr->integer(1)]),
	        "[1; 'abc']" => $vr->set([$vr->integer(1), $vr->string('abc')]),
			'Mutable[Integer, 42]' => $vr->mutable($this->typeRegistry->integer(), $vr->integer(42)),
			"@'error'" => $vr->error($vr->string('error')),
			"type{Boolean}" => $vr->type($this->typeRegistry->boolean),
			"^Null => Any %% Integer :: null" => $vr->function(
				$this->typeRegistry->null,
				$this->typeRegistry->integer(),
				$this->typeRegistry->any,
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$vr->null
					)
				)
			),
        ] as $string => $value) {
			$this->assertEquals($string, (string)$value);
		}
	}

	public function testTypes(): void {
		$this->addSampleTypes();

		$i = fn(string $name) => new TypeNameIdentifier($name);
		$ev = fn(string $name) => new EnumValueIdentifier($name);
		$tr = $this->typeRegistry;

		foreach([
			'MyAlias' => $tr->alias($i('MyAlias')),
			'MyAtom' => $tr->atom($i('MyAtom')),
			'MyEnum' => $tr->enumeration($i('MyEnum')),
			'MyEnum[A, B]' => $tr->enumerationSubsetType($i('MyEnum'), [$ev('A'), $ev('B')]),
			'MySealed' => $tr->sealed($i('MySealed')),
			'MySubtype' => $tr->subtype($i('MySubtype')),
			'Boolean' => $tr->boolean,
			'True' => $tr->true,
			'False' => $tr->false,
			'Null' => $tr->null,
			'Integer' => $tr->integer(),
			'Integer<1..>' => $tr->integer(1),
			'Integer<1..5>' => $tr->integer(1, 5),
			'Integer<..5>' => $tr->integer(MinusInfinity::value, 5),
			'Integer[1, -14]' => $tr->integerSubset([new Number(1), new Number(-14)]),
			'Real' => $tr->real(),
			'Real<3.14..>' => $tr->real(3.14),
			'Real<3.14..5>' => $tr->real(3.14, 5),
			'Real<..5>' => $tr->real(MinusInfinity::value, 5),
			'Real[3.14, -14]' => $tr->realSubset([new Number('3.14'), new Number(-14)]),
			'String' => $tr->string(),
			'String<3..>' => $tr->string(3),
			'String<3..5>' => $tr->string(3, 5),
			'String<..5>' => $tr->string(0, 5),
	        "String['', 'test']" => $tr->stringSubset(['', 'test']),
			'Array' => $tr->array(),
			'Array<3..>' => $tr->array($tr->any, 3),
			'Array<3..5>' => $tr->array($tr->any, 3, 5),
			'Array<..5>' => $tr->array($tr->any, 0, 5),
			'Array<Integer>' => $tr->array($tr->integer()),
			'Array<Integer, 3..>' => $tr->array($tr->integer(), 3),
			'Array<Integer, 3..5>' => $tr->array($tr->integer(), 3, 5),
			'Array<Integer, ..5>' => $tr->array($tr->integer(), 0, 5),

			'Map' => $tr->map(),
			'Map<3..>' => $tr->map($tr->any, 3),
			'Map<3..5>' => $tr->map($tr->any, 3, 5),
			'Map<..5>' => $tr->map($tr->any, 0, 5),
			'Map<Integer>' => $tr->map($tr->integer()),
			'Map<Integer, 3..>' => $tr->map($tr->integer(), 3),
			'Map<Integer, 3..5>' => $tr->map($tr->integer(), 3, 5),
			'Map<Integer, ..5>' => $tr->map($tr->integer(), 0, 5),

	        'Set' => $tr->set(),
            'Set<3..>' => $tr->set($tr->any, 3),
            'Set<3..5>' => $tr->set($tr->any, 3, 5),
            'Set<..5>' => $tr->set($tr->any, 0, 5),
            'Set<Integer>' => $tr->set($tr->integer()),
            'Set<Integer, 3..>' => $tr->set($tr->integer(), 3),
            'Set<Integer, 3..5>' => $tr->set($tr->integer(), 3, 5),
            'Set<Integer, ..5>' => $tr->set($tr->integer(), 0, 5),

			'[]' => $tr->tuple([]),
			'[Boolean, String]' => $tr->tuple([$tr->boolean, $tr->string()]),
			'[...]' => $tr->tuple([], $tr->any),
			'[Boolean, String, ...]' => $tr->tuple([$tr->boolean, $tr->string()], $tr->any),
			'[... Integer]' => $tr->tuple([], $tr->integer()),
			'[Boolean, String, ... Integer]' => $tr->tuple([$tr->boolean, $tr->string()], $tr->integer()),

			'[:]' => $tr->record([]),
			'[a: Integer, b: String]' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()]),
			'[: ...]' => $tr->record([], $tr->any),
			'[a: Integer, b: String, ...]' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()], $tr->any),
	        '[: ... Real]' => $tr->record([], $tr->real()),
            '[a: Integer, b: String, ... Real]' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()], $tr->real()),

			'Mutable<Integer>' => $tr->mutable($tr->integer()),
			'Result<Integer, String>' => $tr->result($tr->integer(), $tr->string()),
			'Type<Boolean>' => $tr->type($tr->boolean),
			'^Null => Any' => $tr->function($tr->null,$tr->any),

			'Any' => $tr->any,
			'Nothing' => $tr->nothing,
			'(MyAtom|MyEnum)' => $tr->union([
				$tr->atom($i('MyAtom')), $tr->enumeration($i('MyEnum'))
			], false),
			'(MyAtom&MyEnum)' => $tr->intersection([
				$tr->atom($i('MyAtom')), $tr->enumeration($i('MyEnum'))
			], false),
			'OptionalKey<Integer>' => $tr->optionalKey($tr->integer()),

	        'Union' => $tr->metaType(MetaTypeValue::Union),
	        //'MyAtom' => $tr->proxyType($i('MyAtom'))
		] as $string => $value) {
			$this->assertEquals($string, (string)$value);
		}
	}

	public function testExpressions(): void {
		$this->addSampleTypes();

		$er = $this->expressionRegistry;
		$c0 = $er->constant($this->valueRegistry->integer(0));
		$x = $er->variableName(new VariableNameIdentifier('x'));

		foreach([
			'0' => $c0,
			'[:]' => $er->record([]),
			'[x: 0]' => $er->record(['x' => $c0]),
			'[]' => $er->tuple([]),
			'[0]' => $er->tuple([$c0]),
			'[;]' => $er->set([]),
			'[0;]' => $er->set([$c0]),
			'[0; x]' => $er->set([$c0, $x]),
			'mutable{Integer, 0}' => $er->mutable($this->typeRegistry->integer(), $c0),
			'x' => $x,
			'x = 0' => $er->variableAssignment(new VariableNameIdentifier('x'), $c0),
			'=> 0' => $er->return($c0),
			'?noError(0)' => $er->noError($c0),
			'?noExternalError(0)' => $er->noExternalError($c0),
			'{0; 0}' => $er->sequence([$c0, $c0]),
			"x->item('a')" => $er->propertyAccess($x, 'a'),
			"x->item('0')" => $er->propertyAccess($x, '0'),
			'x->invoke(0)' => $er->functionCall($x, $c0),
			'0->construct(type{MySubtype})' => $er->constructorCall(new TypeNameIdentifier('MySubtype'), $c0),
			'x->method(0)' => $er->methodCall($x, new MethodNameIdentifier('method'), $c0),
			'x->method' => $er->methodCall($x, new MethodNameIdentifier('method'), $er->constant($this->valueRegistry->null)),
			'?whenIsTrue { x->asBoolean: 0, ~: 0 }' => $er->matchTrue([
				$er->matchPair($x, $c0),
				$er->matchDefault($c0)
			]),
	        '?whenTypeOf (x) is { x: 0, ~: 0 }' => $er->matchType($x, [
                $er->matchPair($x, $c0),
                $er->matchDefault($c0)
            ]),
			'?whenValueOf (x) is { x: 0, ~: 0 }' => $er->matchValue($x, [
				$er->matchPair($x, $c0),
				$er->matchDefault($c0)
			]),
			'?when (x) { 0 }' => $er->matchIf($x, $c0, $er->constant($this->valueRegistry->null)),
			'?when (x) { x } ~ { 0 }' => $er->matchIf($x, $x, $c0),
		] as $string => $value) {
			$this->assertEquals($string, (string)$value);
		}

	}

}