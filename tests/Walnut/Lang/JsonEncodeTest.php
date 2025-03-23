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

final class JsonEncodeTest extends BaseProgramTestHelper {

	public function testValues(): void {
		$this->addSampleTypes();

		$i = fn(string $name) => new TypeNameIdentifier($name);
		$ev = fn(string $name) => new EnumValueIdentifier($name);
		$vr = $this->valueRegistry;
		foreach([
			'{"valueType":"Atom","typeName":"MyAtom"}'
				=> $vr->atom($i('MyAtom')),
			'{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"A"}'
				=> $vr->enumerationValue($i('MyEnum'), $ev('A')),
			'{"valueType":"Sealed","typeName":"MySealed","value":{"valueType":"Null"}}'
				=> $vr->sealedValue($i('MySealed'), $vr->null),
			'{"valueType":"Open","typeName":"MyOpen","value":{"valueType":"Null"}}'
				=> $vr->openValue($i('MyOpen'), $vr->null),
			'{"valueType":"Boolean","value":"true"}' => $vr->boolean(true),
			'{"valueType":"Boolean","value":"false"}' => $vr->boolean(false),
			'{"valueType":"Null"}' => $vr->null,
			'{"valueType":"Integer","value":123}' => $vr->integer(123),
			'{"valueType":"Real","value":123.456}' => $vr->real(123.456),
			'{"valueType":"String","value":"abc"}' => $vr->string('abc'),
			'{"valueType":"Tuple","value":[]}' => $vr->tuple([]),
			'{"valueType":"Tuple","value":[{"valueType":"Integer","value":1},{"valueType":"String","value":"abc"}]}'
				=> $vr->tuple([$vr->integer(1), $vr->string('abc')]),
			'{"valueType":"Record","value":[]}' => $vr->record([]),
	        '{"valueType":"Record","value":{"a":{"valueType":"Integer","value":1},"b":{"valueType":"String","value":"abc"}}}'
	            => $vr->record(['a' => $vr->integer(1), 'b' => $vr->string('abc')]),
			'{"valueType":"Set","value":[]}' => $vr->set([]),
			'{"valueType":"Set","value":[{"valueType":"Integer","value":1}]}' => $vr->set([$vr->integer(1)]),
			'{"valueType":"Set","value":[{"valueType":"Integer","value":1},{"valueType":"String","value":"abc"}]}' => $vr->set([$vr->integer(1), $vr->string('abc')]),
			'{"valueType":"Mutable","targetType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"value":{"valueType":"Integer","value":42}}'
				=> $vr->mutable($this->typeRegistry->integer(), $vr->integer(42)),
			'{"valueType":"Error","errorValue":{"valueType":"String","value":"error"}}' => $vr->error($vr->string('error')),
			'{"valueType":"Type","value":{"type":"Boolean"}}' => $vr->type($this->typeRegistry->boolean),
	        '{"valueType":"Function","parameterType":{"type":"Null"},"returnType":{"type":"Any"},"body":{"expression":{"expressionType":"constant","value":{"valueType":"Null"}}}}'
			        => $vr->function(
				$this->typeRegistry->null,
		        null,
				$this->typeRegistry->integer(),
				$this->typeRegistry->any,
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$vr->null
					)
				)
			),
        ] as $string => $value) {
			$this->assertEquals($string, json_encode($value));
		}
	}

	public function testTypes(): void {
		$this->addSampleTypes();

		$i = fn(string $name) => new TypeNameIdentifier($name);
		$ev = fn(string $name) => new EnumValueIdentifier($name);
		$tr = $this->typeRegistry;

		foreach([
			'{"type":"Alias","name":"MyAlias","aliasedType":{"type":"Null"}}'
				=> $tr->alias($i('MyAlias')),
			'{"type":"Atom","name":"MyAtom"}' => $tr->atom($i('MyAtom')),
			'{"type":"Enumeration","name":"MyEnum","values":{"A":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"A"},"B":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"B"},"C":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"C"}}}'
				=> $tr->enumeration($i('MyEnum')),
			'{"type":"EnumerationSubsetType","enumerationName":"MyEnum","subsetValues":{"A":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"A"},"B":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"B"}}}'
				=> $tr->enumerationSubsetType($i('MyEnum'), [$ev('A'), $ev('B')]),
			'{"type":"Sealed","name":"MySealed","valueType":{"type":"Null"}}'
				=> $tr->sealed($i('MySealed')),
			'{"type":"Open","name":"MyOpen","valueType":{"type":"Null"}}' => $tr->open($i('MyOpen')),
			'{"type":"Boolean"}' => $tr->boolean,
			'{"type":"True"}' => $tr->true,
			'{"type":"False"}' => $tr->false,
			'{"type":"Null"}' => $tr->null,
			'{"type":"Shape","refType":{"type":"Null"}}' => $tr->shape($tr->null),
			'{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}' => $tr->integer(),
			'{"type":"Integer","range":{"minValue":1,"maxValue":"+Infinity"}}' => $tr->integer(1),
			'{"type":"Integer","range":{"minValue":1,"maxValue":5}}' => $tr->integer(1, 5),
			'{"type":"Integer","range":{"minValue":"-Infinity","maxValue":5}}' => $tr->integer(MinusInfinity::value, 5),
			'{"type":"IntegerSubset","values":[1,-14]}' => $tr->integerSubset([new Number(1), new Number(-14)]),
	        '{"type":"Real","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}' => $tr->real(),
			'{"type":"Real","range":{"minValue":3.14,"maxValue":"+Infinity"}}' => $tr->real(3.14),
			'{"type":"Real","range":{"minValue":3.14,"maxValue":5}}' => $tr->real(3.14, 5),
			'{"type":"Real","range":{"minValue":"-Infinity","maxValue":5}}' => $tr->real(MinusInfinity::value, 5),
	        '{"type":"RealSubset","values":[3.14,-14]}' => $tr->realSubset([new Number('3.14'), new Number(-14)]),
			'{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->string(),
			'{"type":"String","range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->string(3),
			'{"type":"String","range":{"minLength":3,"maxLength":5}}' => $tr->string(3, 5),
			'{"type":"String","range":{"minLength":0,"maxLength":5}}' => $tr->string(0, 5),
	        '{"type":"StringSubset","values":["","test"]}' => $tr->stringSubset(['', 'test']),
			'{"type":"Array","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->array(),
			'{"type":"Array","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->array($tr->any, 3),
			'{"type":"Array","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":5}}' => $tr->array($tr->any, 3, 5),
	        '{"type":"Array","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":5}}' => $tr->array($tr->any, 0, 5),
			'{"type":"Array","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->array($tr->integer()),
			'{"type":"Array","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->array($tr->integer(), 3),
			'{"type":"Array","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":5}}' => $tr->array($tr->integer(), 3, 5),
			'{"type":"Array","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":5}}' => $tr->array($tr->integer(), 0, 5),
			'{"type":"Map","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->map(),
			'{"type":"Map","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->map($tr->any, 3),
			'{"type":"Map","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":5}}' => $tr->map($tr->any, 3, 5),
			'{"type":"Map","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":5}}' => $tr->map($tr->any, 0, 5),
			'{"type":"Map","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->map($tr->integer()),
			'{"type":"Map","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->map($tr->integer(), 3),
			'{"type":"Map","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":5}}' => $tr->map($tr->integer(), 3, 5),
			'{"type":"Map","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":5}}' => $tr->map($tr->integer(), 0, 5),
			'{"type":"Set","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->set(),
			'{"type":"Set","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->set($tr->any, 3),
			'{"type":"Set","itemType":{"type":"Any"},"range":{"minLength":3,"maxLength":5}}' => $tr->set($tr->any, 3, 5),
	        '{"type":"Set","itemType":{"type":"Any"},"range":{"minLength":0,"maxLength":5}}' => $tr->set($tr->any, 0, 5),
			'{"type":"Set","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":"+Infinity"}}' => $tr->set($tr->integer()),
			'{"type":"Set","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":"+Infinity"}}' => $tr->set($tr->integer(), 3),
			'{"type":"Set","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":3,"maxLength":5}}' => $tr->set($tr->integer(), 3, 5),
			'{"type":"Set","itemType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"range":{"minLength":0,"maxLength":5}}' => $tr->set($tr->integer(), 0, 5),

			'{"type":"Tuple","types":[],"restType":{"type":"Nothing"}}' => $tr->tuple([]),
			'{"type":"Tuple","types":[{"type":"Boolean"},{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}],"restType":{"type":"Nothing"}}' => $tr->tuple([$tr->boolean, $tr->string()]),
			'{"type":"Tuple","types":[],"restType":{"type":"Any"}}' => $tr->tuple([], $tr->any),
			'{"type":"Tuple","types":[{"type":"Boolean"},{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}],"restType":{"type":"Any"}}' => $tr->tuple([$tr->boolean, $tr->string()], $tr->any),
			'{"type":"Tuple","types":[],"restType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->tuple([], $tr->integer()),
			'{"type":"Tuple","types":[{"type":"Boolean"},{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}],"restType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->tuple([$tr->boolean, $tr->string()], $tr->integer()),

			'{"type":"Record","types":[],"restType":{"type":"Nothing"}}' => $tr->record([]),
			'{"type":"Record","types":{"a":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"b":{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}},"restType":{"type":"Nothing"}}' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()]),
			'{"type":"Record","types":[],"restType":{"type":"Any"}}' => $tr->record([], $tr->any),
			'{"type":"Record","types":{"a":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"b":{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}},"restType":{"type":"Any"}}' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()], $tr->any),
	        '{"type":"Record","types":[],"restType":{"type":"Real","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->record([], $tr->real()),
            '{"type":"Record","types":{"a":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"b":{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}},"restType":{"type":"Real","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->record(['a' => $tr->integer(), 'b' => $tr->string()], $tr->real()),

			'{"type":"Mutable","valueType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->mutable($tr->integer()),
			'{"type":"Result","returnType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"errorType":{"type":"String","range":{"minLength":0,"maxLength":"+Infinity"}}}' => $tr->result($tr->integer(), $tr->string()),
			'{"type":"Type","refType":{"type":"Boolean"}}' => $tr->type($tr->boolean),
			'{"type":"Function","parameter":{"type":"Null"},"return":{"type":"Any"}}' => $tr->function($tr->null,$tr->any),

			'{"type":"Any"}' => $tr->any,
			'{"type":"Nothing"}' => $tr->nothing,
			'{"type":"Union","types":[{"type":"Atom","name":"MyAtom"},{"type":"Enumeration","name":"MyEnum","values":{"A":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"A"},"B":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"B"},"C":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"C"}}}]}' => $tr->union([
				$tr->atom($i('MyAtom')), $tr->enumeration($i('MyEnum'))
			], false),
			'{"type":"Intersection","types":[{"type":"Atom","name":"MyAtom"},{"type":"Enumeration","name":"MyEnum","values":{"A":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"A"},"B":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"B"},"C":{"valueType":"EnumerationValue","typeName":"MyEnum","valueIdentifier":"C"}}}]}' => $tr->intersection([
				$tr->atom($i('MyAtom')), $tr->enumeration($i('MyEnum'))
			], false),
			'{"type":"OptionalKey","valueType":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}}}' => $tr->optionalKey($tr->integer()),

	        '{"type":"MetaType","metaType":"Union"}' => $tr->metaType(MetaTypeValue::Union),
	        //'MyAtom' => $tr->proxyType($i('MyAtom'))
		] as $string => $value) {
			$this->assertEquals($string, json_encode($value));
		}
	}


	public function testExpressions(): void {
		$this->addSampleTypes();

		$er = $this->expressionRegistry;
		$c0 = $er->constant($this->valueRegistry->integer(0));
		$x = $er->variableName(new VariableNameIdentifier('x'));

		foreach([
			'{"expressionType":"constant","value":{"valueType":"Integer","value":0}}' => $c0,
			'{"expressionType":"Record","values":[]}' => $er->record([]),
			'{"expressionType":"Record","values":{"x":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}}' => $er->record(['x' => $c0]),
			'{"expressionType":"Tuple","values":[]}' => $er->tuple([]),
			'{"expressionType":"Tuple","values":[{"expressionType":"constant","value":{"valueType":"Integer","value":0}}]}' => $er->tuple([$c0]),
			'{"expressionType":"Set","values":[]}' => $er->set([]),
			'{"expressionType":"Set","values":[{"expressionType":"constant","value":{"valueType":"Integer","value":0}}]}' => $er->set([$c0]),
			'{"expressionType":"Set","values":[{"expressionType":"constant","value":{"valueType":"Integer","value":0}},{"expressionType":"variableName","variableName":"x"}]}' => $er->set([$c0, $x]),
			'{"expressionType":"Mutable","type":{"type":"Integer","range":{"minValue":"-Infinity","maxValue":"+Infinity"}},"value":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->mutable($this->typeRegistry->integer(), $c0),
			'{"expressionType":"variableName","variableName":"x"}' => $x,
			'{"expressionType":"variableAssignment","variableName":"x","assignedExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->variableAssignment(new VariableNameIdentifier('x'), $c0),
			'{"expressionType":"return","returnedExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->return($c0),
			'{"expressionType":"noError","targetExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->noError($c0),
			'{"expressionType":"noExternalError","targetExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->noExternalError($c0),
			'{"expressionType":"sequence","expressions":[{"expressionType":"constant","value":{"valueType":"Integer","value":0}},{"expressionType":"constant","value":{"valueType":"Integer","value":0}}]}' => $er->sequence([$c0, $c0]),
			'{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"item","parameter":{"expressionType":"constant","value":{"valueType":"String","value":"a"}}}' => $er->propertyAccess($x, 'a'),
			'{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"item","parameter":{"expressionType":"constant","value":{"valueType":"String","value":"0"}}}' => $er->propertyAccess($x, '0'),
			'{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"invoke","parameter":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->functionCall($x, $c0),
			'{"expressionType":"methodCall","target":{"expressionType":"constant","value":{"valueType":"Integer","value":0}},"methodName":"construct","parameter":{"expressionType":"constant","value":{"valueType":"Type","value":{"type":"Open","name":"MyOpen","valueType":{"type":"Null"}}}}}' => $er->constructorCall(new TypeNameIdentifier('MyOpen'), $c0),
			'{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"method","parameter":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->methodCall($x, new MethodNameIdentifier('method'), $c0),
			'{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"method","parameter":{"expressionType":"constant","value":{"valueType":"Null"}}}' => $er->methodCall($x, new MethodNameIdentifier('method'), $er->constant($this->valueRegistry->null)),
			'{"expressionType":"Match","target":{"expressionType":"constant","value":{"valueType":"Boolean","value":"true"}},"operation":"equals","pairs":[{"matchExpression":{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"asBoolean","parameter":{"expressionType":"constant","value":{"valueType":"Null"}}},"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}},{"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}]}' => $er->matchTrue([
				$er->matchPair($x, $c0),
				$er->matchDefault($c0)
			]),
	        '{"expressionType":"Match","target":{"expressionType":"variableName","variableName":"x"},"operation":"isSubtypeOf","pairs":[{"matchExpression":{"expressionType":"variableName","variableName":"x"},"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}},{"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}]}' => $er->matchType($x, [
                $er->matchPair($x, $c0),
                $er->matchDefault($c0)
            ]),
			'{"expressionType":"Match","target":{"expressionType":"variableName","variableName":"x"},"operation":"equals","pairs":[{"matchExpression":{"expressionType":"variableName","variableName":"x"},"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}},{"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}]}' => $er->matchValue($x, [
				$er->matchPair($x, $c0),
				$er->matchDefault($c0)
			]),
			'{"expressionType":"Match","target":{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"asBoolean","parameter":{"expressionType":"constant","value":{"valueType":"Null"}}},"operation":"equals","pairs":[{"matchExpression":{"expressionType":"constant","value":{"valueType":"Boolean","value":"true"}},"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}},{"valueExpression":{"expressionType":"constant","value":{"valueType":"Null"}}}]}' => $er->matchIf($x, $c0, $er->constant($this->valueRegistry->null)),
			'{"expressionType":"Match","target":{"expressionType":"methodCall","target":{"expressionType":"variableName","variableName":"x"},"methodName":"asBoolean","parameter":{"expressionType":"constant","value":{"valueType":"Null"}}},"operation":"equals","pairs":[{"matchExpression":{"expressionType":"constant","value":{"valueType":"Boolean","value":"true"}},"valueExpression":{"expressionType":"variableName","variableName":"x"}},{"valueExpression":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}]}' => $er->matchIf($x, $x, $c0),

			'{"expressionType":"MatchError","target":{"expressionType":"variableName","variableName":"x"},"onError":{"expressionType":"constant","value":{"valueType":"Integer","value":0}},"else":{"expressionType":"constant","value":{"valueType":"Integer","value":0}}}' => $er->matchError($x, $c0, $c0)
		] as $string => $value) {
			$this->assertEquals($string, json_encode($value));
		}

	}

}