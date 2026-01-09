<?php

namespace Walnut\Lang\Test\Implementation\NativeCode;

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

final class CastAsTest extends BaseProgramTestHelper {

	private function callCastAs(Value $value, Type $type, Value $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'as',
			$this->expressionRegistry->constant(
				$this->valueRegistry->type($type)
			),
			$expected
		);
	}

	public function testCastAs(): void {
		$this->callCastAs(
			$this->valueRegistry->integer(1),
			$this->typeRegistry->boolean,
			$this->valueRegistry->true
		);
		$this->callCastAs(
			$this->valueRegistry->integer(0),
			$this->typeRegistry->boolean,
			$this->valueRegistry->false
		);

		$this->typeRegistryBuilder->addEnumeration(
			new TypeNameIdentifier('OrderStatus'), [
				new EnumValueIdentifier('Invalid'), 
				new EnumValueIdentifier('Draft'), 
				new EnumValueIdentifier('Completed')
			]
		);
		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->enumeration(new TypeNameIdentifier('OrderStatus')),
			new MethodNameIdentifier('asBoolean'),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->boolean,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->matchValue(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('$')
					),
					[
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Invalid')
								)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->false
							)
						),
						$this->expressionRegistry->matchDefault(
							$this->expressionRegistry->constant(
								$this->valueRegistry->true
							)
						),
					]
				),
			)
		);
		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Draft')
			),
			$this->typeRegistry->boolean,
			$this->valueRegistry->true
		);
		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Invalid')
			),
			$this->typeRegistry->boolean,
			$this->valueRegistry->false
		);

		$this->customMethodRegistryBuilder->addMethod(
			$enumType = $this->typeRegistry->enumeration(new TypeNameIdentifier('OrderStatus')),
			new MethodNameIdentifier('asInteger'),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(0, 2),
			$toInt = $this->expressionRegistry->functionBody(
				$this->expressionRegistry->matchValue(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('$')
					),
					[
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Invalid')
								)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->integer(0)
							)
						),
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Draft')
								)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->integer(1)
							)
						),
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Completed')
								)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->integer(2)
							)
						)
					],
				),
			)
		);
		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Invalid')
			),
			$this->typeRegistry->integer(),
			$this->valueRegistry->integer(0)
		);
		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Draft')
			),
			$this->typeRegistry->integer(),
			$this->valueRegistry->integer(1)
		);
		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Completed')
			),
			$this->typeRegistry->integer(),
			$this->valueRegistry->integer(2)
		);


		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->integer(),
			new MethodNameIdentifier('as' . $enumType->name),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$enumType,
			$fromInt = $this->expressionRegistry->functionBody(
				$this->expressionRegistry->matchValue(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('$')
					),
					[
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->integer(1)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Draft')
								)
							),
						),
						$this->expressionRegistry->matchPair(
							$this->expressionRegistry->constant(
								$this->valueRegistry->integer(2)
							),
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Completed')
								)
							),
						),
						$this->expressionRegistry->matchDefault(
							$this->expressionRegistry->constant(
								$this->valueRegistry->enumerationValue(
									new TypeNameIdentifier('OrderStatus'),
									new EnumValueIdentifier('Invalid')
								)
							),
						),
					],
				),
			)
		);

		$this->callCastAs(
			$this->valueRegistry->integer(0),
			$enumType,
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Invalid')
			),
		);
		$this->callCastAs(
			$this->valueRegistry->integer(1),
			$enumType,
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Draft')
			),
		);
		$this->callCastAs(
			$this->valueRegistry->integer(2),
			$enumType,
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Completed')
			),
		);

		$j = new TypeNameIdentifier('JsonValue');
		$aliasType = $this->typeRegistry->proxyType($j);
	    //$jsonValueType =
		$this->typeRegistry->addAlias($j,
		    $this->typeRegistry->union([
				$this->typeRegistry->null,
			    $this->typeRegistry->boolean,
			    $this->typeRegistry->integer(),
			    $this->typeRegistry->real(),
			    $this->typeRegistry->string(),
			    $this->typeRegistry->array($aliasType),
			    $this->typeRegistry->map($aliasType),
			    $this->typeRegistry->result($this->typeRegistry->nothing, $aliasType),
			    $this->typeRegistry->mutable($aliasType)
		    ], false),
	    );


		//$this->callCastAs(
		//	$this->valueRegistry->string("1"),
			$jv = $this->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));//,
		//	$this->valueRegistry->string("1")
		//);

		$this->customMethodRegistryBuilder->addMethod(
			$enumType,
			new MethodNameIdentifier('asJsonValue'),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(0, 2),
			$toInt
		);

		$this->callCastAs(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('OrderStatus'),
				new EnumValueIdentifier('Draft')
			),
			$this->typeRegistry->alias(new TypeNameIdentifier('JsonValue')),
			$this->valueRegistry->integer(1)
		);

		$this->customMethodRegistryBuilder->addMethod(
			$jv,
			new MethodNameIdentifier('as' . $enumType->name),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$enumType,
			$fromInt
		);

		$call = $this->expressionRegistry->methodCall(
			$this->expressionRegistry->variableName(new VariableNameIdentifier('x')),
			new MethodNameIdentifier('as'),
			$this->expressionRegistry->constant(
				$this->valueRegistry->type($enumType)
			)
		);
		$call->analyse(new AnalyserContext($this->programRegistry->typeRegistry, $this->programRegistry->methodFinder, new VariableScope(['x' => $jv])));
		$this->assertTrue($call->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'x' => 	($this->valueRegistry->integer(1))
				])))
				->value->equals(
					$this->valueRegistry->enumerationValue(
						new TypeNameIdentifier('OrderStatus'),
						new EnumValueIdentifier('Draft')
					)
				)
		);

	}
}
