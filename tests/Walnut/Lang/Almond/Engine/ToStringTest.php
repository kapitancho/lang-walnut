<?php

namespace Walnut\Lang\Test\Almond\Engine;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class ToStringTest extends AlmondBaseTestHelper {

	public function testValues(): void {
		$this->addSampleTypes();
		$i = fn(string $name) => new TypeName($name);
		$ev = fn(string $name) => new EnumerationValueName($name);
		$vr = $this->valueRegistry;
		foreach([
			'MyAtom' => $vr->atom($i('MyAtom')),
			'MyEnum.A' => $vr->enumeration($i('MyEnum'), $ev('A')),
			'MySealed{null}' => $vr->sealed($i('MySealed'), $vr->null),
			'MyOpen{null}' => $vr->open($i('MyOpen'), $vr->null),
			'MyData!null' => $vr->data($i('MyData'), $vr->null),
			'true' => $vr->boolean(true),
			'false' => $vr->boolean(false),
			'null' => $vr->null,
			'123' => $vr->integer(123),
			'123.456' => $vr->real(123.456),
			"'abc'" => $vr->string('abc'),
	        '"abc"' => $vr->bytes('abc'),
			'[]' => $vr->tuple([]),
			"[1, 'abc']" => $vr->tuple([$vr->integer(1), $vr->string('abc')]),
			'[:]' => $vr->record([]),
	        "[a: 1, b: 'abc']" => $vr->record(['a' => $vr->integer(1), 'b' => $vr->string('abc')]),
			'[;]' => $vr->set([]),
	        "[1;]" => $vr->set([$vr->integer(1)]),
	        "[1; 'abc']" => $vr->set([$vr->integer(1), $vr->string('abc')]),
			'mutable{Integer, 42}' => $vr->mutable($this->typeRegistry->integer(), $vr->integer(42)),
			"@'error'" => $vr->error($vr->string('error')),
			"type{Boolean}" => $vr->type($this->typeRegistry->boolean),
			/*TODO: "^Null => Any %% Integer :: null" => $vr->function(
				$this->typeRegistry->nameAndType(
					$this->typeRegistry->null,
					null
				),
				$this->typeRegistry->nameAndType(
					$this->typeRegistry->integer(),
					null
				),
				$this->typeRegistry->any,
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$vr->null
					)
				)
			),*/
        ] as $string => $value) {
			$this->assertEquals($string, (string)$value);
		}
	}

	public function testTypes(): void {
		$this->addSampleTypes();

		$i = fn(string $name) => new TypeName($name);
		$ev = fn(string $name) => new EnumerationValueName($name);
		$tr = $this->typeRegistry;

		foreach([
			'MyAlias' => $tr->userland->alias($i('MyAlias')),
			'MyAtom' => $tr->userland->atom($i('MyAtom')),
			'MyEnum' => $tr->userland->enumeration($i('MyEnum')),
			'MyEnum[A, B]' => $tr->userland->enumerationSubsetType($i('MyEnum'), [$ev('A'), $ev('B')]),
			'MySealed' => $tr->userland->sealed($i('MySealed')),
			'MyOpen' => $tr->userland->open($i('MyOpen')),
			'MyData' => $tr->userland->data($i('MyData')),
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
	        'Bytes' => $tr->bytes(),
	        'Bytes<3..>' => $tr->bytes(3),
	        'Bytes<3..5>' => $tr->bytes(3, 5),
	        'Bytes<..5>' => $tr->bytes(0, 5),
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
			'Map<String<2>:Integer>' => $tr->map($tr->integer(), keyType: $tr->string(2, 2)),
			'Map<String<1..5>:Integer, 3..5>' => $tr->map($tr->integer(), 3, 5, $tr->string(1, 5)),
			"Map<String['a', 'b']:Integer, 3..5>" => $tr->map($tr->integer(), 3, 5, $tr->stringSubset(['a', 'b'])),
			"Map<NonEmptyString:Integer, 3..5>" => $tr->map($tr->integer(), 3, 5, $tr->typeByName(new TypeName('NonEmptyString'))),

	        'Set' => $tr->set(),
            'Set<3..>' => $tr->set($tr->any, 3),
            'Set<3..5>' => $tr->set($tr->any, 3, 5),
            'Set<..5>' => $tr->set($tr->any, 0, 5),
            'Set<Integer>' => $tr->set($tr->integer()),
            'Set<Integer, 3..>' => $tr->set($tr->integer(), 3),
            'Set<Integer, 3..5>' => $tr->set($tr->integer(), 3, 5),
            'Set<Integer, ..5>' => $tr->set($tr->integer(), 0, 5),

			'[]' => $tr->tuple([], null),
			'[Boolean, String]' => $tr->tuple([$tr->boolean, $tr->string()], null),
			'[...]' => $tr->tuple([], $tr->any),
			'[Boolean, String, ...]' => $tr->tuple([$tr->boolean, $tr->string()], $tr->any),
			'[... Integer]' => $tr->tuple([], $tr->integer()),
			'[Boolean, String, ... Integer]' => $tr->tuple([$tr->boolean, $tr->string()], $tr->integer()),

			'[:]' => $tr->record([], null),
			'[a: Integer, b: String]' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()], null),
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
				$tr->userland->atom($i('MyAtom')), $tr->userland->enumeration($i('MyEnum'))
			]),
			'(MyAtom&MyEnum)' => $tr->intersection([
				$tr->userland->atom($i('MyAtom')), $tr->userland->enumeration($i('MyEnum'))
			]),
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
		$x = $er->variableName(new VariableName('x'));

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
			'A!0' => $er->data(new TypeName('A'), $c0),
			'x' => $x,
			'x = 0' => $er->variableAssignment(new VariableName('x'), $c0),
			'var{x} = 0' => $er->multiVariableAssignment([new VariableName('x')], $c0),
			'var{x, y} = 0' => $er->multiVariableAssignment([new VariableName('x'), new VariableName('y')], $c0),
			'var{a: x} = 0' => $er->multiVariableAssignment(['a' => new VariableName('x')], $c0),
			'var{~x} = 0' => $er->multiVariableAssignment(['x' => new VariableName('x')], $c0),
			'var{a: x, ~y} = 0' => $er->multiVariableAssignment(['a' => new VariableName('x'), 'y' => new VariableName('y')], $c0),
			':: 0' => $er->scoped($c0),
			'=> 0' => $er->return($c0),
			'(0)?' => $er->noError($c0),
			'(0)*?' => $er->noExternalError($c0),
			'(0)' => $er->group($c0),
			'{0; 0}' => $er->sequence([$c0, $c0]),
			"x->item('a')" => $er->propertyAccess($x, 'a'),
			"x->item('0')" => $er->propertyAccess($x, '0'),
			'x->invoke(0)' => $er->functionCall($x, $c0),
			'0->construct(type{MyOpen})' => $er->constructorCall(new TypeName('MyOpen'), $c0),
			'x->method(0)' => $er->methodCall($x, new MethodName('method'), $c0),
			'x->method' => $er->methodCall($x, new MethodName('method'), $er->constant($this->valueRegistry->null)),
	        "(0) && (x)" => $er->booleanAnd($c0, $x),
			"(0) || (x)" => $er->booleanOr($c0, $x),
	        '?whenIsTrue { x->asBoolean: 0, ~: 0 }' => $er->matchTrue([
				$er->matchPair($x, $c0),
	        ], $er->matchDefault($c0)),
	        '?whenTypeOf (x) { x: 0, ~: 0 }' => $er->matchType($x, [
                $er->matchPair($x, $c0)],
                $er->matchDefault($c0)),
			'?whenValueOf (x) { x: 0, ~: 0 }' => $er->matchValue($x, [
				$er->matchPair($x, $c0)],
				$er->matchDefault($c0)),
			'?when (x) { 0 }' => $er->matchIf($x, $c0, $er->constant($this->valueRegistry->null)),
			'?when (x) { x } ~ { 0 }' => $er->matchIf($x, $x, $c0),
			'?whenIsError (x) { 0 }' => $er->matchError($x, $c0, null),
			'?whenIsError (x) { x } ~ { 0 }' => $er->matchError($x, $x, $c0),
		] as $string => $value) {
			$this->assertEquals($string, (string)$value);
		}

	}

}