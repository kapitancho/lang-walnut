<?php

namespace Walnut\Lang\Test\Implementation\NativeCode\JsonValue;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class HydrateAsTest extends BaseProgramTestHelper {

	private function constructorCall(TypeNameIdentifier $typeName, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$parameter,
			new MethodNameIdentifier('construct'),
			$this->expressionRegistry->constant(
				$this->valueRegistry->type(
					$this->typeRegistry->typeByName($typeName)
				)
			)
		);
	}

	private function callHydrateAs(Value $value, Type $type, Value $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->methodCall(
				$this->expressionRegistry->constant($value),
				new MethodNameIdentifier('as'),
				$this->expressionRegistry->constant($this->valueRegistry->type(
					$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
				))
			),
			'hydrateAs',
			$this->expressionRegistry->constant($this->valueRegistry->type($type)),
			$expected
		);
	}

	private function callHydrateAsError(Value $value, Type $type, string $expected): void {
		$target = $this->expressionRegistry->methodCall(
			$this->expressionRegistry->constant($value),
			new MethodNameIdentifier('as'),
			$this->expressionRegistry->constant($this->valueRegistry->type(
				$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
			))
		);
		$call = $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('hydrateAs'),
			$this->expressionRegistry->constant($this->valueRegistry->type($type))
		);
		$call->analyse(new AnalyserContext($this->programRegistry, new VariableScope([])));
		$this->assertEquals($expected, (string)
			$call->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])))->value
		);
	}

    private function analyseCallHydrateAs(Type $type): void {
        $this->testMethodCallAnalyse(
			$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
	        'hydrateAs',
            $this->typeRegistry->type($type),
            $this->typeRegistry->result(
				$type, $this->typeRegistry->withName(new TypeNameIdentifier("HydrationError"))
            )
        );
    }

	public function testHydrateAs(): void {
		$this->typeRegistry->addAtom(
			new TypeNameIdentifier('MyAtom'),
		);

		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->withName(new TypeNameIdentifier('MyAtom')),
			new MethodNameIdentifier('asJsonValue'),
			$this->typeRegistry->null,
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant(
					$this->valueRegistry->integer(2)
				)
			)
		);

		$this->typeRegistry->addEnumeration(
			new TypeNameIdentifier('MyEnum'),[
				new EnumValueIdentifier('A'),
				new EnumValueIdentifier('B'),
				new EnumValueIdentifier('C'),
				new EnumValueIdentifier('D')
			]
		);

		$this->typeRegistry->addAlias(
			new TypeNameIdentifier('MyAlias'),
			$this->typeRegistry->integer(1, 5)
		);

		$this->typeRegistry->addData(
			new TypeNameIdentifier('MyData'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer()]),
		);

		$this->typeRegistry->addOpen(
			new TypeNameIdentifier('MyOpen'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer()]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			),
			null
		);

		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('MySealed'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer()]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			),
			null
		);

		$this->typeRegistry->addEnumeration(
			new TypeNameIdentifier('MyCustomEnum'),[
				new EnumValueIdentifier('A'),
				new EnumValueIdentifier('B'),
				new EnumValueIdentifier('C'),
			]
		);

		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier('asMyCustomEnum'),
			$this->typeRegistry->null,
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->enumeration(new TypeNameIdentifier('MyCustomEnum')),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->matchValue(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('$')
					),
					[
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->string('A')
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('MyCustomEnum'),
									new EnumValueIdentifier('A')
								)
							)
						),
						$this->expressionRegistry->matchDefault(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('MyCustomEnum'),
									new EnumValueIdentifier('B')
								)
							)
						),
					]
				),
			)
		);

		$this->typeRegistry->addSealed(
			new TypeNameIdentifier('MyCustomState'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->string()]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			),
			null
		);

		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue')),
			new MethodNameIdentifier('asMyCustomState'),
			$this->typeRegistry->null,
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->sealed(new TypeNameIdentifier('MyCustomState')),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->matchValue(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('$')
					),
					[
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->string('A')
							),
							$this->constructorCall(
								new TypeNameIdentifier('MyCustomState'),
								$this->expressionRegistry->constant(
									$this->valueRegistry->record(['x' => $this->valueRegistry->string('A')])
								)
							)
						),
						$this->expressionRegistry->matchDefault(
							$this->constructorCall(
								new TypeNameIdentifier('MyCustomState'),
								$this->expressionRegistry->constant(
									$this->valueRegistry->record(['x' => $this->valueRegistry->string('B')])
								)
							)
						),
					]
				),
			)
		);

		//Integer
		$this->callHydrateAs(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->integer(),
			$this->valueRegistry->integer(123)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->integer(1, 100),
			"@HydrationError![\n	value: 123,\n	hydrationPath: 'value',\n	errorMessage: 'The integer value should be in the range 1..100'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->integer(),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be an integer in the range -Infinity..+Infinity'\n]"
		);
		$this->callHydrateAs(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->integerSubset([
				new Number(1),
				new Number(5),
				new Number(123),
			]),
			$this->valueRegistry->integer(123)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->integerSubset([
				new Number(1),
				new Number(5),
			]),
			"@HydrationError![\n	value: 123,\n	hydrationPath: 'value',\n	errorMessage: 'The integer value should be among 1, 5'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->integerSubset([
				new Number(1),
				new Number(5),
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be an integer among 1, 5'\n]"
		);

		//Real
		$this->callHydrateAs(
			$this->valueRegistry->real(12.3),
			$this->typeRegistry->real(),
			$this->valueRegistry->real(12.3)
		);
		$this->callHydrateAs(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->real(),
			$this->valueRegistry->real(123)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->real(12.3),
			$this->typeRegistry->real(1, 9.99),
			"@HydrationError![\n	value: 12.3,\n	hydrationPath: 'value',\n	errorMessage: 'The real value should be in the range 1..9.99'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->real(),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a real number in the range -Infinity..+Infinity'\n]"
		);
		$this->callHydrateAs(
			$this->valueRegistry->real(12.3),
			$this->typeRegistry->realSubset([
				new Number(1),
				new Number('3.14'),
				new Number('12.3'),
			]),
			$this->valueRegistry->real(12.3)
		);
		$this->callHydrateAs(
			$this->valueRegistry->integer(1),
			$this->typeRegistry->realSubset([
				new Number(1),
				new Number('3.14'),
				new Number('12.3'),
			]),
			$this->valueRegistry->real(1)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->real(12.3),
			$this->typeRegistry->realSubset([
				new Number(1),
				new Number('3.14'),
			]),
			"@HydrationError![\n	value: 12.3,\n	hydrationPath: 'value',\n	errorMessage: 'The real value should be among 1, 3.14'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->realSubset([
				new Number(1),
				new Number('3.14'),
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a real number among 1, 3.14'\n]"
		);

		//String
		$this->callHydrateAs(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->string(),
			$this->valueRegistry->string('hello')
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->string(10, 100),
			"@HydrationError![\n	value: 'hello',\n	hydrationPath: 'value',\n	errorMessage: 'The string value should be with a length between 10 and 100'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->string(),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]"
		);
		$this->callHydrateAs(
			$this->valueRegistry->string('hi!'),
			$this->typeRegistry->stringSubset([
				'hello',
				'world',
				'hi!',
			]),
			$this->valueRegistry->string('hi!')
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hi!'),
			$this->typeRegistry->stringSubset([
				'hello',
				'world',
			]),
			"@HydrationError![\n	value: 'hi!',\n	hydrationPath: 'value',\n	errorMessage: 'The string value should be among hello, world'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->stringSubset([
				'hello',
				'world',
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string among hello, world'\n]"
		);

		//Boolean
		$this->callHydrateAs(
			$this->valueRegistry->true,
			$this->typeRegistry->boolean,
			$this->valueRegistry->true
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->boolean,
			"@HydrationError![\n	value: 'hello',\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a boolean'\n]"
		);

		//True
		$this->callHydrateAs(
			$this->valueRegistry->true,
			$this->typeRegistry->true,
			$this->valueRegistry->true
		);
		$this->callHydrateAsError(
			$this->valueRegistry->false,
			$this->typeRegistry->true,
			"@HydrationError![\n	value: false,\n	hydrationPath: 'value',\n	errorMessage: 'The boolean value should be true'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->true,
			"@HydrationError![\n	value: 'hello',\n	hydrationPath: 'value',\n	errorMessage: 'The value should be \\`true\\`'\n]"
		);

		//False
		$this->callHydrateAs(
			$this->valueRegistry->false,
			$this->typeRegistry->false,
			$this->valueRegistry->false
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->false,
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The boolean value should be false'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->false,
			"@HydrationError![\n	value: 'hello',\n	hydrationPath: 'value',\n	errorMessage: 'The value should be \\`false\\`'\n]"
		);

		//Null
		$this->callHydrateAs(
			$this->valueRegistry->null,
			$this->typeRegistry->null,
			$this->valueRegistry->null
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('hello'),
			$this->typeRegistry->null,
			"@HydrationError![\n	value: 'hello',\n	hydrationPath: 'value',\n	errorMessage: 'The value should be \\`null\\`'\n]"
		);

		//Array
		if (0) $this->callHydrateAs(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->array(),
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
		);
		if (0) $this->callHydrateAsError(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->array($this->typeRegistry->any, 10, 100),
			"@HydrationError![\n	value: [42, 'Hello'],\n	hydrationPath: 'value',\n	errorMessage: 'The array value should be with a length between 10 and 100'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->array(),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be an array with a length between 0 and +Infinity'\n]"
		);
		if (0) $this->callHydrateAsError(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->array($this->typeRegistry->integer()),
			"@HydrationError![\n	value: 'Hello',\n	hydrationPath: 'value'[1],\n	errorMessage: 'The value should be an integer in the range -Infinity..+Infinity'\n]"
		);

		//Map
		$this->callHydrateAs(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->map(),
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
		);
		$this->callHydrateAsError(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->map($this->typeRegistry->any, 10, 100),
			"@HydrationError![\n	value: [a: 42, b: 'Hello'],\n	hydrationPath: 'value',\n	errorMessage: 'The map value should be with a length between 10 and 100'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->map(),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a map with a length between 0 and +Infinity'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->map($this->typeRegistry->integer()),
			"@HydrationError![\n	value: 'Hello',\n	hydrationPath: 'value.b',\n	errorMessage: 'The value should be an integer in the range -Infinity..+Infinity'\n]"
		);

		//Tuple
		if (0) $this->callHydrateAs(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			]),
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
		);
		if (0) $this->callHydrateAsError(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->tuple([
				$this->typeRegistry->any,
				$this->typeRegistry->string(),
				$this->typeRegistry->boolean,
			]),
			"@HydrationError![\n	value: [42, 'Hello'],\n	hydrationPath: 'value',\n	errorMessage: 'The tuple value should be with 3 items'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->tuple([]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a tuple with 0 items'\n]"
		);
		if (0) $this->callHydrateAsError(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(42),
				$this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(10, 100),
			]),
			"@HydrationError![\n	value: 'Hello',\n	hydrationPath: 'value[1]',\n	errorMessage: 'The string value should be with a length between 10 and 100'\n]"
		);

		//Map
		$this->callHydrateAs(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
			]),
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
		);
		$this->callHydrateAsError(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->any,
				'b' => $this->typeRegistry->string(),
				'c' => $this->typeRegistry->boolean,
			]),
			"@HydrationError![\n	value: [a: 42, b: 'Hello'],\n	hydrationPath: 'value',\n	errorMessage: 'The record value should contain the key c'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->any,
				'c' => $this->typeRegistry->boolean,
			]),
			"@HydrationError![\n	value: [a: 42, b: 'Hello'],\n	hydrationPath: 'value',\n	errorMessage: 'The record value should contain the key c'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->record([]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a record with 0 items'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(42),
				'b' => $this->valueRegistry->string("Hello"),
			]),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(10, 100),
			]),
			"@HydrationError![\n	value: 'Hello',\n	hydrationPath: 'value.b',\n	errorMessage: 'The string value should be with a length between 10 and 100'\n]"
		);

		//Alias
		$this->callHydrateAs(
			$this->valueRegistry->integer(3),
			$this->typeRegistry->withName(new TypeNameIdentifier('MyAlias')),
			$this->valueRegistry->integer(3)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->withName(new TypeNameIdentifier('MyAlias')),
			"@HydrationError![\n	value: 123,\n	hydrationPath: 'value',\n	errorMessage: 'The integer value should be in the range 1..5'\n]"
		);

		//Union
		$this->callHydrateAs(
			$this->valueRegistry->integer(3),
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			]),
			$this->valueRegistry->integer(3)
		);
		$this->callHydrateAs(
			$this->valueRegistry->integer(3),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
			]),
			$this->valueRegistry->integer(3)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->boolean(true),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->integer(),
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string with a length between 0 and +Infinity'\n]"
		);

		//Intersection
		//TODO
		/*$this->callHydrateAsError(
			$this->valueRegistry->boolean(true),
			$this->typeRegistry->intersection([
				$this->typeRegistry->record(['a' => $this->typeRegistry->string()]),
				$this->typeRegistry->record(['b' => $this->typeRegistry->integer()]),
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a record with 1 items\n]"
		);*/

		//Result
		$this->callHydrateAs(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->string()),
			$this->valueRegistry->integer(123)
		);
		$this->callHydrateAs(
			$this->valueRegistry->string("hello"),
			$this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->string()),
			$this->valueRegistry->error($this->valueRegistry->string("hello"))
		);
		$this->callHydrateAsError(
			$this->valueRegistry->boolean(true),
			$this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->string()),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be an integer in the range -Infinity..+Infinity'\n]"
		);

		//Mutable
		$this->callHydrateAs(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->mutable($this->typeRegistry->integer()),
			$this->valueRegistry->mutable(
				$this->typeRegistry->integer(),
				$this->valueRegistry->integer(123)
			)
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->mutable($this->typeRegistry->integer()),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be an integer in the range -Infinity..+Infinity'\n]"
		);

		//Type
		$this->callHydrateAs(
			$this->valueRegistry->string("MyData"),
			$this->typeRegistry->type($this->typeRegistry->any),
			$this->valueRegistry->type($this->typeRegistry->withName(new TypeNameIdentifier('MyData')))
		);

		$this->callHydrateAs(
			$this->valueRegistry->string("MyOpen"),
			$this->typeRegistry->type($this->typeRegistry->any),
			$this->valueRegistry->type($this->typeRegistry->withName(new TypeNameIdentifier('MyOpen')))
		);

		$this->callHydrateAs(
			$this->valueRegistry->string("MySealed"),
			$this->typeRegistry->type($this->typeRegistry->any),
			$this->valueRegistry->type($this->typeRegistry->withName(new TypeNameIdentifier('MySealed')))
		);

		$this->callHydrateAsError(
			$this->valueRegistry->integer(123),
			$this->typeRegistry->type($this->typeRegistry->integer()),
			"@HydrationError![\n	value: 123,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string, containing a name of a valid type'\n]"
		);

		//Atom
		$this->callHydrateAs(
			$this->valueRegistry->integer(42),
			$this->typeRegistry->atom(new TypeNameIdentifier('MyAtom')),
			$this->valueRegistry->atom(new TypeNameIdentifier('MyAtom')),
		);
		$this->callHydrateAs(
			$this->valueRegistry->integer(42),
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->atom(new TypeNameIdentifier('MyAtom'))
			]),
			$this->valueRegistry->atom(new TypeNameIdentifier('MyAtom')),
		);

		//Enumeration
		$this->callHydrateAs(
			$this->valueRegistry->string('C'),
			$this->typeRegistry->enumeration(new TypeNameIdentifier('MyEnum')),
			$this->valueRegistry->enumerationValue(new TypeNameIdentifier('MyEnum'), new EnumValueIdentifier('C')),
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->enumeration(new TypeNameIdentifier('MyEnum')),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string with a value among MyEnum.A, MyEnum.B, MyEnum.C, MyEnum.D'\n]"
		);
		$this->callHydrateAs(
			$this->valueRegistry->string('C'),
			$this->typeRegistry->enumerationSubsetType(new TypeNameIdentifier('MyEnum'), [
				new EnumValueIdentifier('A'),
				new EnumValueIdentifier('B'),
				new EnumValueIdentifier('C'),
			]),
			$this->valueRegistry->enumerationValue(new TypeNameIdentifier('MyEnum'), new EnumValueIdentifier('C')),
		);
		$this->callHydrateAsError(
			$this->valueRegistry->string('C'),
			$this->typeRegistry->enumerationSubsetType(new TypeNameIdentifier('MyEnum'), [
				new EnumValueIdentifier('A'),
				new EnumValueIdentifier('B'),
			]),
			"@HydrationError![\n	value: 'C',\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string with a value among MyEnum.A, MyEnum.B'\n]"
		);
		$this->callHydrateAsError(
			$this->valueRegistry->true,
			$this->typeRegistry->enumerationSubsetType(new TypeNameIdentifier('MyEnum'), [
				new EnumValueIdentifier('A'),
				new EnumValueIdentifier('B'),
			]),
			"@HydrationError![\n	value: true,\n	hydrationPath: 'value',\n	errorMessage: 'The value should be a string with a value among MyEnum.A, MyEnum.B'\n]"
		);

		$this->callHydrateAs(
			$this->valueRegistry->string('A'),
			$this->typeRegistry->enumeration(new TypeNameIdentifier('MyCustomEnum')),
			$this->valueRegistry->enumerationValue(new TypeNameIdentifier('MyCustomEnum'), new EnumValueIdentifier('A')),
		);
		$this->callHydrateAs(
			$this->valueRegistry->true,
			$this->typeRegistry->enumeration(new TypeNameIdentifier('MyCustomEnum')),
			$this->valueRegistry->enumerationValue(new TypeNameIdentifier('MyCustomEnum'), new EnumValueIdentifier('B')),
		);

		//Data
		$this->callHydrateAs(
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)]),
			$this->typeRegistry->withName(new TypeNameIdentifier('MyData')),
			$this->valueRegistry->dataValue(
				new TypeNameIdentifier('MyData'),
				$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)])
			)
		);

		//Open
		$this->callHydrateAs(
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)]),
			$this->typeRegistry->withName(new TypeNameIdentifier('MyOpen')),
			$this->valueRegistry->openValue(
				new TypeNameIdentifier('MyOpen'),
				$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)])
			)
		);

		//Sealed
		$this->callHydrateAs(
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)]),
			$this->typeRegistry->withName(new TypeNameIdentifier('MySealed')),
			$this->valueRegistry->sealedValue(
				new TypeNameIdentifier('MySealed'),
				$this->valueRegistry->record(['x' => $this->valueRegistry->integer(123)])
			)
		);

		$this->callHydrateAs(
			$this->valueRegistry->string('A'),
			$this->typeRegistry->sealed(new TypeNameIdentifier('MyCustomState')),
			$this->valueRegistry->sealedValue(new TypeNameIdentifier('MyCustomState'),
				$this->valueRegistry->record(['x' => $this->valueRegistry->string('A')])),
		);
		$this->callHydrateAs(
			$this->valueRegistry->true,
			$this->typeRegistry->sealed(new TypeNameIdentifier('MyCustomState')),
			$this->valueRegistry->sealedValue(new TypeNameIdentifier('MyCustomState'),
				$this->valueRegistry->record(['x' => $this->valueRegistry->string('B')])),
		);

		$value = '{"a":true,"b":123,"c":[{"x":15},{"x":20}],"d":{"x":"B","y":2}}';

		$result = $this->expressionRegistry->methodCall(
			//$this->expressionRegistry->noError(
				$this->expressionRegistry->methodCall(
					$this->expressionRegistry->constant($this->valueRegistry->string($value)),
					new MethodNameIdentifier('jsonDecode'),
					$this->expressionRegistry->constant($this->valueRegistry->null)
				//)
			),
			new MethodNameIdentifier('hydrateAs'),
			$this->expressionRegistry->constant($this->valueRegistry->type(
				$this->typeRegistry->record([
					'a' => $this->typeRegistry->boolean,
					'b' => $this->typeRegistry->integer(),
					'c' => $this->typeRegistry->array($this->typeRegistry->withName(
						new TypeNameIdentifier('MySealed')
					)),
					'd' => $this->typeRegistry->record([
						'x' => $this->typeRegistry->enumeration(new TypeNameIdentifier('MyCustomEnum')),
						'y' => $this->typeRegistry->atom(new TypeNameIdentifier('MyAtom')),
					]),
				])
			))
		)->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])));
		$this->assertEquals(
			"[\n\ta: true,\n\tb: 123,\n\tc: [MySealed[x: 15], MySealed[x: 20]],\n\td: [x: MyCustomEnum.B, y: MyAtom]\n]",
			(string)$result->value
		);
		$back = $this->expressionRegistry->methodCall(
			$this->expressionRegistry->methodCall(
				$this->expressionRegistry->constant($result->value),
				new MethodNameIdentifier('asJsonValue'),
				$this->expressionRegistry->constant($this->valueRegistry->null)
				/*$this->expressionRegistry->constant($this->valueRegistry->type(
					$this->typeRegistry->withName(new TypeNameIdentifier('JsonValue'))
				))*/
			),
			new MethodNameIdentifier('stringify'),
			$this->expressionRegistry->constant($this->valueRegistry->null)
		)->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])));

		$this->assertEquals(
			"'" . $value . "'",
			str_replace(["\n", '\n', "\t", ' '], '', (string)$back->value)
		);

		$result = $this->expressionRegistry->methodCall(
			$this->expressionRegistry->constant($this->valueRegistry->string('invalid json')),
			new MethodNameIdentifier('jsonDecode'),
			$this->expressionRegistry->constant($this->valueRegistry->null)
		)->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])));

		$this->assertEquals(
			"@InvalidJsonString![value: 'invalid json']",
			(string)$result->value
		);

	}
}
