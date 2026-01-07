<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\AST\Parser;

use BcMath\Number;
use ReflectionClass;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\AST\Parser\ParserState as ParserStateInterface;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\AST\Node\SourceLocation;
use Walnut\Lang\Implementation\AST\Parser\Token as T;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lib\Walex\PatternMatch;
use Walnut\Lib\Walex\Token as LT;

final readonly class ParserStateMachine {
	public function __construct(
		private ParserStateInterface $s,
		private NodeBuilder $nodeBuilder,
		private EscapeCharHandler $stringEscapeCharHandler,
		private EscapeCharHandler $bytesEscapeCharHandler
	) {}

	public function getAllStates(): array {
		return [
			-1 => ['name' => 'EOF', 'transitions' => [
				'' => function(LT $token) {
					$this->s->move(-1);
				},
			]],
			100 => ['name' => 'module start', 'transitions' => [
				T::module_identifier->name => function(LT $token) {
					$moduleId = substr($token->patternMatch->text, 7, -1);
					if (str_contains($moduleId, '%%')) {
						[$moduleName, $dependencyNames] = explode('%%', $moduleId);
						$dependencyNames = array_map('trim', explode(',', $dependencyNames));
					} else {
						$moduleName = $moduleId;
						$dependencyNames = [];
					}
					$this->nodeBuilder
						->moduleName($moduleName)
						->moduleDependencies($dependencyNames);

					$this->s->move(101);
				}
			]],
			101 => ['name' => 'module content', 'transitions' => [
				'EOF' => -1,
				'' => function (LT $token) {
					$this->s->push(102);
					$this->s->stay(103);
				}
			]],
			102 => ['name' => 'module content separator', 'transitions' => [
				T::expression_separator->name => 101
			]],
			103 => ['name' => 'module content start', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->result['dependencyName'] = null;
					$this->s->result['dependencyType'] = $this->nodeBuilder->nothingType;
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(112);
					$this->s->move(2000);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(113);
					$this->s->move(625);
				},
				T::type_keyword->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['typeName'] = $token->patternMatch->text;
					$this->s->move(104);
				},
				T::cast_marker->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['typeName'] = 'DependencyContainer';
					$this->s->move(116);
				},
			]],
			104 => ['name' => 'module level type definition', 'transitions' => [
				T::named_type->name => 114,
				T::assign->name => 105,
				T::cast_marker->name => 116,
				T::method_marker->name => 119,
				T::call_start->name => function(LT $token) {
					$this->s->push(129);
					$this->s->move(605);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->tupleType([]);
					$this->s->move(131);
				},
				T::empty_record->name => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->recordType([]);
					$this->s->move(131);
				},
				T::tuple_start->name => 130,
			]],
			105 => ['name' => 'module level type assignment', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(126);
					$this->s->stay(4000);
				},
				T::arithmetic_op_multiply->name => $c,
				T::sequence_start->name => $c, 
				T::lambda_param->name => $c,
				T::tuple_start->name => $c,
				T::call_start->name => $c,
				T::empty_tuple->name => 837,
				T::empty_record->name => 838,
			]],
			106 => ['name' => 'module level atom', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAtom(
							new TypeNameIdentifier($this->s->result['typeName'])
						)
					);
					$this->s->pop();
				}
			]],
			107 => ['name' => 'module level enum', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->result['enumerationValues'] ??= [];
					$this->s->result['enumerationValues'][] = $token->patternMatch->text;
					$this->s->move(108);
				},
				T::var_keyword->name => $c
			]],
			108 => ['name' => 'module level enum separator', 'transitions' => [
				T::value_separator->name => 107,
				T::call_end->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addEnumeration(
							new TypeNameIdentifier($this->s->result['typeName']),
							array_map(
								static fn(string $value): EnumValueIdentifier => new EnumValueIdentifier($value),
								$this->s->result['enumerationValues'] ?? []
							)
						)
					);
					$this->s->moveAndPop();
				}
			]],
			109 => ['name' => 'sealed type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(147);
					$this->s->stay(4000);
				},
				T::arithmetic_op_multiply->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c, 
				T::lambda_param->name => $c,
			]],
			110 => ['name' => 'open type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(142);
					$this->s->stay(4000);
				},
				T::arithmetic_op_multiply->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c, 
				T::lambda_param->name => $c,
			]],
			111 => ['name' => 'data type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(139);
					$this->s->stay(4000);
				},
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::arithmetic_op_multiply->name => $c,
				T::type_proxy_keyword->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c,
				T::lambda_param->name => $c,
			]],
			112 => ['name' => 'module level cli entry point return', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addMethod(
							$this->nodeBuilder->namedType(
								new TypeNameIdentifier('DependencyContainer')
							),
							new MethodNameIdentifier('asCliEntryPoint'),
							$this->nodeBuilder->nameAndType(
								$this->nodeBuilder->nullType,
								null,
							),
							$this->nodeBuilder->nameAndType(
								$this->nodeBuilder->nothingType,
								null
							),
							$this->nodeBuilder->namedType(
								new TypeNameIdentifier('CliEntryPoint')
							),
							$this->nodeBuilder->functionBody(
								$this->nodeBuilder->constant(
									$this->nodeBuilder->functionValue(
										$this->nodeBuilder->nameAndType(
											$this->nodeBuilder->arrayType(
												$this->nodeBuilder->stringType()
											),
											new VariableNameIdentifier('args')
										),
										$this->nodeBuilder->nameAndType(
											$this->s->result['dependencyType'],
											$this->s->result['dependencyName'] ?? null
										),
										$this->nodeBuilder->stringType(),
										$this->nodeBuilder->functionBody(
											$this->s->generated
										)
									)
								)
							),
						)
					);
					$this->s->pop();
				},
			]],
			113 => ['name' => 'module level cli entry point dependency type return', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->result['dependencyName'] = $this->s->generated['dependency_name'];
					$this->s->result['dependencyType'] = $this->s->generated['dependency_type'];
					$this->s->push(112);
					$this->s->move(2000);
				},
			]],
			114 => ['name' => 'module level named type creation', 'transitions' => [
				T::call_start->name => 115,
				T::special_var_param->name => 110,
				T::this_var->name => 109,
				'' => function(LT $token) {
					$this->s->stay(111);
				},
			]],
			115 => ['name' => 'module level atom or enum', 'transitions' => [
				T::call_end->name => 106,
				'' => function(LT $token) {
					$this->s->result['enumerationValues'] = [];
					$this->s->stay(107);
				},
			]],
			116 => ['name' => 'cast base type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['castToTypeName'] = $token->patternMatch->text;
					$this->s->move(117);
				}
			]],
			117 => ['name' => 'cast body marker', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(124);
					$this->s->move(4000);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->move(141);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(118);
					$this->s->move(2000);
				},
			]],
			118 => ['name' => 'cast body result', 'transitions' => [
				'' => function(LT $token) {
					$errorType = $this->s->result['error_type'] ?? null;
					$returnType = $this->nodeBuilder->namedType(
						new TypeNameIdentifier($this->s->result['castToTypeName'])
					);
					if ($errorType) {
						$returnType = $this->nodeBuilder->resultType($returnType, $errorType);
					}
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addMethod(
							$this->nodeBuilder->namedType(
								new TypeNameIdentifier($this->s->result['typeName'])
							),
							new MethodNameIdentifier('as' . $this->s->result['castToTypeName']),
							$this->nodeBuilder->nameAndType(
								$this->nodeBuilder->nullType,
								null
							),
							$this->nodeBuilder->nameAndType(
								$this->s->result['dependency_type'] ??
									$this->nodeBuilder->nothingType,
								$this->s->result['dependency_name'] ?? null
							),
							$returnType,
							$this->nodeBuilder->functionBody($this->s->generated),
						)
					);
					$this->s->pop();
				}
			]],
			119 => ['name' => 'method definition start', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['method_name'] = $token->patternMatch->text;
					$this->s->move(120);
				},
				T::type_keyword->name => $c,
				T::val->name => $c,
			]],
			120 => ['name' => 'method name next', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->push(121);
					$this->s->move(602);
				}
			]],
			121 => ['name' => 'method name next parameter', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->nodeBuilder->functionType(
						$this->s->generated['parameter_type'],
						$this->s->generated['return_type'],
					);
					$this->s->move(122);
				}
			]],
			122 => ['name' => 'method name body', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(136);
					$this->s->move(625);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(123);
					$this->s->move(2000);
				},
			]],
			123 => ['name' => 'method name result', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addMethod(
							$this->nodeBuilder->namedType(
								new TypeNameIdentifier($this->s->result['typeName'])
							),
							new MethodNameIdentifier($this->s->result['method_name']),
							$this->nodeBuilder->nameAndType(
								$this->s->result['parameter_type']->parameterType,
								$this->s->result['parameter_name'] ?? null
							),
							$this->nodeBuilder->nameAndType(
								$this->s->result['dependency_type'] ??
									$this->nodeBuilder->nothingType,
								$this->s->result['dependency_name'] ?? null
							),
							$this->s->result['parameter_type']->returnType,
							$this->nodeBuilder->functionBody($this->s->generated),
						)
					);
					$this->s->pop();
				}
			]],
			124 => ['name' => 'cast error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(125);
				}
			]],
			125 => ['name' => 'cast error type return', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->move(141);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(118);
					$this->s->move(2000);
				},
			]],
			126 => ['name' => 'module level type alias end', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->generated
						)
					);
					$this->s->pop();
				},
			]],
			129 => ['name' => 'constructor method parameter', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->s->generated['parameter_type'];
					$this->s->move(131);
				},
			]],
			130 => ['name' => 'constructor method parameter tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(133);
					$this->s->back(4000);
				}
			]],
			131 => ['name' => 'constructor method body', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(134);
					$this->s->move(4000);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(137);
					$this->s->move(625);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(132);
					$this->s->move(2000);
				},
			]],
			132 => ['name' => 'constructor method result', 'transitions' => [
				'' => function(LT $token, ParserState $state) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addConstructorMethod(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->nodeBuilder->nameAndType(
								$this->s->result['parameter_type'],
								$this->s->result['parameter_name'] ?? null
							),
							$this->nodeBuilder->nameAndType(
								$this->s->result['dependency_type'] ??
									$this->nodeBuilder->nothingType,
								$this->s->result['dependency_name'] ?? null
							),
							$this->s->result['error_type'] ?? null,
							$this->nodeBuilder->functionBody($this->s->generated)
						)
					);
					$this->s->pop();
				}
			]],
			133 => ['name' => 'constructor method tuple or record parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->s->generated;
					$this->s->stay(131);
				}
			]],
			134 => ['name' => 'constructor method error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(135);
				}
			]],
			135 => ['name' => 'constructor method body after error type', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(137);
					$this->s->move(625);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(132);
					$this->s->move(2000);
				},
			]],
			136 => ['name' => 'method dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(123);
					$this->s->move(2000);
				},
			]],
			137 => ['name' => 'constructor dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(132);
					$this->s->move(2000);
				},
			]],
			138 => ['name' => 'cast dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(118);
					$this->s->move(2000);
				}
			]],
			139 => ['name' => 'data base type return', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addData(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->generated,
						)
					);
					$this->s->pop();
				}
			]],
			140 => ['name' => 'cast dependency result shortcut', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated;
					$this->s->push(118);
					$this->s->move(2000);
				},
				T::expression_separator->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated;
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->nodeBuilder->variableName(
							new VariableNameIdentifier('%')
						),
						new MethodNameIdentifier('as'),
						$this->nodeBuilder->constant(
							$this->nodeBuilder->typeValue(
								$this->nodeBuilder->namedType(
									new TypeNameIdentifier($this->s->result['castToTypeName'])
								)
							)
						)
					);
					$this->s->stay(118);
				},
			]],
			141 => ['name' => 'cast dependency type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->push(140);
					$this->s->stay(4000);
				},
				'' => function(LT $token) {
					$this->s->push(138);
					$this->s->stay(625);
				},
			]],
			142 => ['name' => 'open base type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['value_type'] = $this->s->generated;
					$this->s->stay(143);
				}
			]],
			143 => ['name' => 'open error type', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(144);
					$this->s->move(4000);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(146);
					$this->s->move(2000);
				},
				T::expression_separator->name => function(LT $token) {
					$this->s->generated = null; /*$this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);*/
					$this->s->stay(146);
				},
			]],
			144 => ['name' => 'open error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(145);
				}
			]],
			145 => ['name' => 'open error type body start', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(146);
					$this->s->move(2000);
				},
			]],
			146 => ['name' => 'open result', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addOpen(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->result['value_type'],
							$this->s->generated,
							$this->s->result['error_type'] ?? null,
						)
					);
					$this->s->pop();
				}
			]],
			147 => ['name' => 'sealed base type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['value_type'] = $this->s->generated;
					$this->s->stay(148);
				}
			]],
			148 => ['name' => 'sealed error type', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(149);
					$this->s->move(4000);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(151);
					$this->s->move(2000);
				},
				T::expression_separator->name => function(LT $token) {
					$this->s->generated = null;/*$this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);*/
					$this->s->stay(151);
				},
			]],
			149 => ['name' => 'sealed error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(150);
				}
			]],
			150 => ['name' => 'sealed error type body start', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(151);
					$this->s->move(2000);
				},
			]],
			151 => ['name' => 'sealed result', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addSealed(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->result['value_type'],
							$this->s->generated,
							$this->s->result['error_type'] ?? null,
						)
					);
					$this->s->pop();
				}
			]],
			202 => ['name' => 'constant expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(203);
					$this->s->stay(401);
				}
			]],
			203 => ['name' => 'constant expression value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant($this->s->generated);
					$this->s->pop();
				}
			]],
			204 => ['name' => 'expression sequence', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(205);
					$this->s->stay(3000);
				}
			]],
			205 => ['name' => 'expression sequence separator', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->s->result['sequence_expressions'][] = $this->s->generated;
					$this->s->push(205);
					$this->s->move(3000);
				},
				T::sequence_end->name => function(LT $token) {
					$g = $this->s->generated;
					if (!(
						$g instanceof SequenceExpressionNode &&
						count($g->expressions) === 0 &&
						count($this->s->result['sequence_expressions']) > 0
					)) {
						$this->s->result['sequence_expressions'][] = $this->s->generated;
					}
					$this->s->generated = $this->nodeBuilder->sequence($this->s->result['sequence_expressions']);
					$this->s->moveAndPop();
				}
			]],
			206 => ['name' => 'expression group', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(207);
					$this->s->stay(3000);
				}
			]],
			207 => ['name' => 'expression sequence separator', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->group($this->s->generated);
					$this->s->moveAndPop();
				}
			]],
			208 => ['name' => 'expression no error', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->push(209);
					$this->s->move(3000);
				}
			]],
			209 => ['name' => 'expression no error return', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$result = $this->s->generated;
					$this->s->i++;
					$this->s->generated = $this->nodeBuilder->noError($result);
					$this->s->pop();
				},
			]],
			210 => ['name' => 'expression no external error', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->push(211);
					$this->s->move(3000);
				}
			]],
			211 => ['name' => 'expression no external error return', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$result = $this->s->generated;
					$this->s->i++;
					$this->s->generated = $this->nodeBuilder->noExternalError($result);
					$this->s->pop();
				},
			]],
			212 => ['name' => 'var expression', 'transitions' => [
				T::assign->name => 213,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->variableName(
						new VariableNameIdentifier($this->s->result['var_name'])
					);
					$this->s->pop();
				}
			]],
			213 => ['name' => 'assign expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(214);
					$this->s->stay(3000);
				}
			]],
			214 => ['name' => 'assign expression value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->variableAssignment(
						new VariableNameIdentifier($this->s->result['var_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			215 => ['name' => 'value data expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->data(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			216 => ['name' => 'type expression', 'transitions' => [
				T::boolean_op_not->name => function (LT $token) {
					$this->s->push(215);
					$this->s->move(3160);
				},
				T::property_accessor->name => function(LT $token) {
					$this->s->push(220);
					$this->s->back(401);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->tupleValue([])
					);
					$this->s->move(221);
				},
				T::empty_set->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->setValue([])
					);
					$this->s->move(221);
				},
				T::empty_record->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->recordValue([])
					);
					$this->s->move(221);
				},
				T::call_start->name => 217,
				T::tuple_start->name => function(LT $token) {
					$this->s->push(219);
					$this->s->stay(3160);
				},
				'' => function (LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->atomValue(
							new TypeNameIdentifier($this->s->result['type_name'])
						)
					);
					$this->s->pop();
				}
			]],
			217 => ['name' => 'constructor call expression', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);
					$this->s->stay(218);
				},
				'' => function(LT $token) {
					$this->s->push(218);
					$this->s->stay(3000);
				}
			]],
			218 => ['name' => 'constructor call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->moveAndPop();
				}
			]],
			219 => ['name' => 'constructor call value tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			220 => ['name' => 'enum value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant($this->s->generated);
					$this->s->pop();
				}
			]],
			221 => ['name' => 'constructor call empty value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			222 => ['name' => 'list or dict expression', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->stringEscapeCharHandler->unescape(
								$token->patternMatch->text
							)
						),
						$token->sourcePosition
					);
					$this->s->move(223);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(223);
				},
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				'' => function(LT $token) {
					$this->s->stay(229);
				},
			]],
			223 => ['name' => 'list or dict expression separator', 'transitions' => [
				T::colon->name => 224,
				'' => function(LT $token) {
					$this->s->back(229);
				}
			]],
			224 => ['name' => 'dict expression expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['current_key'] ??= $this->s->result['first_token']->patternMatch->text;
					$this->s->push(225);
					$this->s->stay(3000);
				},
			]],
			225 => ['name' => 'dict expression dict expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(226);
				}
			]],
			226 => ['name' => 'dict expression dict expression separator', 'transitions' => [
				T::tuple_end->name => 227,
				T::value_separator->name => 228,
			]],
			227 => ['name' => 'dict expression dict expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->record(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			228 => ['name' => 'dict expression dict expression key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->stringEscapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(241);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(241);
				},
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				T::mutable->name => $c,
				T::val->name => $c,
			]],
			229 => ['name' => 'list or set expression expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(230);
					$this->s->stay(3000);
				},
			]],
			230 => ['name' => 'list or set expression list expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(231);
				}
			]],
			231 => ['name' => 'list or set expression list expression separator', 'transitions' => [
				T::tuple_end->name => 236,
				T::value_separator->name => 233,
				T::expression_separator->name => 232,
			]],
			232 => ['name' => 'list expression set expression', 'transitions' => [
				T::tuple_end->name => 240,
				'' => function(LT $token) {
					$this->s->stay(237);
				},
			]],
			233 => ['name' => 'list expression tuple expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(234);
					$this->s->stay(3000);
				},
			]],
			234 => ['name' => 'list expression list expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(235);
				}
			]],
			235 => ['name' => 'list expression list expression separator', 'transitions' => [
				T::tuple_end->name => 236,
				T::value_separator->name => 233,
			]],
			236 => ['name' => 'list expression list expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tuple(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			237 => ['name' => 'list expression set expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(238);
					$this->s->stay(3000);
				},
			]],
			238 => ['name' => 'list expression set expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(239);
				}
			]],
			239 => ['name' => 'list expression set expression separator', 'transitions' => [
				T::tuple_end->name => 240,
				T::expression_separator->name => 237,
			]],
			240 => ['name' => 'list expression set expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->set(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			241 => ['name' => 'dict expression separator', 'transitions' => [
				T::colon->name => 224
			]],
			4000 => ['name' => 'type function expression start', 'transitions' => [
				T::lambda_param->name => -4001,
				'' => function(LT $token) {
					$this->s->stay(4010);
				},
			]],
			4001 => ['name' => 'type function expression param', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->nodeBuilder->nullType;
					$this->s->push(4002);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->push(4003);
					$this->s->stay(4010);
				},
			]],
			4002 => ['name' => 'type function expression op return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionType(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			4003 => ['name' => 'type function expression op', 'transitions' => [
				T::lambda_return->name => function(LT $token) { //OK
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(4002);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionType(
						$this->s->generated,
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				}
			]],
			4010 => ['name' => 'type union expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(4011);
					$this->s->stay(4020);
				},
			]],
			4011 => ['name' => 'type union expression op', 'transitions' => [
				T::union->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(4012);
					$this->s->move(4020);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			4012 => ['name' => 'type union expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->unionType(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(4011);
				}
			]],
			4020 => ['name' => 'type intersection expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(4021);
					$this->s->stay(4030);
				},
			]],
			4021 => ['name' => 'type intersection expression op', 'transitions' => [
				T::intersection->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(4022);
					$this->s->move(4030);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			4022 => ['name' => 'type intersection expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->intersectionType(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(4021);
				}
			]],
			4030 => ['name' => 'type impure expression start', 'transitions' => [
				T::arithmetic_op_multiply->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(4031);
					$this->s->move(4040);
				},
				'' => function(LT $token) {
					$this->s->stay(4040);
				},
			]],
			4031 => ['name' => 'type impure expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated =
						$this->nodeBuilder->impureType(
							$this->s->generated,
						);
					$this->s->pop();
				}
			]],
			4040 => ['name' => 'type shape expression start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(4041);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->stay(4050);
				},
			]],
			4041 => ['name' => 'type shape expression op expr', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated =
						$this->nodeBuilder->shapeType(
							$this->s->generated,
						);
					$this->s->moveAndPop();
				}
			]],
			4050 => ['name' => 'type expression start', 'transitions' => [
				T::type_proxy_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] ??= $token->sourcePosition;
					$type = substr($token->patternMatch->text, 1);
					$this->s->result['typeName'] = $type;
					$this->s->state = match($type) {
						'Integer' => 709,
						'Real' => 718,
						'String' => 727,
						'Bytes' => 7271,
						'Array' => 735,
						'Set' => 827,
						'Map' => 745,
						'Type' => 756,
						'Impure' => 761,
						'Mutable' => 766,
						'Result' => 776,
						'Error' => 771,
						'Shape' => 820,
						'Any', 'Nothing', 'Boolean', 'True', 'False', 'Null',
						'MutableValue', 'Enumeration' => 706,
						default => 784
					};
					$this->s->i++;
				},
				T::type_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] ??= $token->sourcePosition;
					$this->s->result['typeName'] = $token->patternMatch->text;
					$this->s->state = match($token->patternMatch->text) {
						'Integer' => 709,
						'Real' => 718,
						'String' => 727,
						'Bytes' => 7271,
						'Array' => 735,
						'Set' => 827,
						'Map' => 745,
						'Shape' => 820,
						'Type' => 756,
						'Impure' => 761,
						'Mutable' => 766,
						'Error' => 771,
						'Result' => 776,
						'Any', 'Nothing', 'Boolean', 'True', 'False', 'Null',
						'MutableValue', 'Enumeration' => 706,
						default => 785
					};
					$this->s->i++;
				},
				T::call_start->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(4051);
					$this->s->move(4000);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType([]);
					$this->s->moveAndPop();
				},
				T::empty_record->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType([]);
					$this->s->moveAndPop();
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(839);
				},
			]],
			4051 => ['name' => 'type expression braces', 'transitions' => [
				T::call_end->name => function(LT $token) {
					if ($startPosition = $this->s->result['startPosition'] ?? null) {
						$sourceLocation = new SourceLocation(
							$this->s->generated->sourceLocation->moduleName,
							$startPosition,
							$token->sourcePosition,
						);
						// Ugly hack to overwrite the source location
						$ref = new ReflectionClass($g = $this->s->generated);
						$new = $ref->newInstanceWithoutConstructor();
						foreach ($ref->getProperties() as $prop) {
							if ($prop->getName() === 'sourceLocation') {
								$prop->setValue($new, $sourceLocation);
							} else {
								$prop->setValue($new, $prop->getValue($g));
							}
						}
						$this->s->generated = $new;
					}
					$this->s->moveAndPop();
				},
			]],

			2000 => ['name' => 'function body midpoint', 'transitions' => [
				'' => function(LT $token) {
					$this->s->stay(3000);
				},
			]],

			3000 => ['name' => 'return scope expression start', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->push(3001);
					$this->s->move(3010);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(3002);
					$this->s->move(3000);
				},
				'' => function(LT $token) {
					$this->s->stay(3010);
				},
			]],
			3001 => ['name' => 'return scope expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->return(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			3002 => ['name' => 'return scope expression scoped', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->scoped(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			3010 => ['name' => 'or expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3011);
					$this->s->stay(3020);
				},
			]],
			3011 => ['name' => 'or expression op', 'transitions' => [
				T::boolean_op_or->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3012);
					$this->s->move(3020);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3012 => ['name' => 'or expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->booleanOr(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(3011);
				}
			]],

			3020 => ['name' => 'xor expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3021);
					$this->s->stay(3030);
				},
			]],
			3021 => ['name' => 'xor expression op', 'transitions' => [
				T::boolean_op_xor->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3022);
					$this->s->move(3030);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3022 => ['name' => 'xor expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->booleanXor(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(3021);
				}
			]],

			3030 => ['name' => 'or else expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3031);
					$this->s->stay(3040);
				},
			]],
			3031 => ['name' => 'or else expression op', 'transitions' => [
				T::or_else->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3032);
					$this->s->move(3040);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3032 => ['name' => 'or else expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier('binaryOrElse'),
						$this->s->generated
					);
					$this->s->stay(3031);
				}
			]],


			3040 => ['name' => 'and expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3041);
					$this->s->stay(3050);
				},
			]],
			3041 => ['name' => 'and expression op', 'transitions' => [
				T::boolean_op_and->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3042);
					$this->s->move(3050);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3042 => ['name' => 'and expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->booleanAnd(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(3041);
				}
			]],

			3050 => ['name' => 'eq expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3051);
					$this->s->stay(3060);
				},
			]],
			3051 => ['name' => 'eq expression op', 'transitions' => [
				T::equals->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryEqual';
					$this->s->push(3052);
					$this->s->move(3060);
				},
				T::not_equals->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryNotEqual';
					$this->s->push(3052);
					$this->s->move(3060);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3052 => ['name' => 'eq expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier($this->s->result['op']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],



			3060 => ['name' => 'rel expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3061);
					$this->s->stay(3070);
				},
			]],
			3061 => ['name' => 'rel expression op', 'transitions' => [
				T::greater_than_equal->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryGreaterThanEqual';
					$this->s->push(3062);
					$this->s->move(3070);
				},
				T::less_than_equal->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryLessThanEqual';
					$this->s->push(3062);
					$this->s->move(3070);
				},
				T::type_start->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryLessThan';
					$this->s->push(3062);
					$this->s->move(3070);
				},
				T::type_end->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryGreaterThan';
					$this->s->push(3062);
					$this->s->move(3070);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3062 => ['name' => 'rel expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier($this->s->result['op']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],


			3070 => ['name' => 'bitwise or expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3071);
					$this->s->stay(3080);
				},
			]],
			3071 => ['name' => 'bitwise or expression op', 'transitions' => [
				T::union->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3072);
					$this->s->move(3080);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3072 => ['name' => 'bitwise or expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier('binaryBitwiseOr'),
						$this->s->generated
					);
					$this->s->stay(3071);
				}
			]],

			3080 => ['name' => 'bitwise xor expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3081);
					$this->s->stay(3090);
				},
			]],
			3081 => ['name' => 'bitwise xor expression op', 'transitions' => [
				T::lambda_param->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3082);
					$this->s->move(3090);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3082 => ['name' => 'bitwise xor expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier('binaryBitwiseXor'),
						$this->s->generated
					);
					$this->s->stay(3081);
				}
			]],


			3090 => ['name' => 'bitwise and expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3091);
					$this->s->stay(3100);
				},
			]],
			3091 => ['name' => 'bitwise and expression op', 'transitions' => [
				T::intersection->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3092);
					$this->s->move(3100);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3092 => ['name' => 'bitwise and expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier('binaryBitwiseAnd'),
						$this->s->generated
					);
					$this->s->stay(3091);
				}
			]],



			3100 => ['name' => 'additive expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3101);
					$this->s->stay(3110);
				},
			]],
			3101 => ['name' => 'additive expression op', 'transitions' => [
				T::arithmetic_op_plus->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryPlus';
					$this->s->push(3102);
					$this->s->move(3110);
				},
				T::arithmetic_op_minus->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryMinus';
					$this->s->push(3102);
					$this->s->move(3110);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3102 => ['name' => 'additive expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier($this->s->result['op']),
						$this->s->generated
					);
					$this->s->stay(3101);
				}
			]],


			3110 => ['name' => 'multiplicative expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3111);
					$this->s->stay(3120);
				},
			]],
			3111 => ['name' => 'multiplicative expression op', 'transitions' => [
				T::special_var_modulo->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryModulo';
					$this->s->push(3112);
					$this->s->move(3120);
				},
				T::arithmetic_op_multiply->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryMultiply';
					$this->s->push(3112);
					$this->s->move(3120);
				},
				T::arithmetic_op_intdiv->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryIntegerDivide';
					$this->s->push(3112);
					$this->s->move(3120);
				},
				T::arithmetic_op_divide->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['op'] = 'binaryDivide';
					$this->s->push(3112);
					$this->s->move(3120);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3112 => ['name' => 'multiplicative expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier($this->s->result['op']),
						$this->s->generated
					);
					$this->s->stay(3111);
				}
			]],
			3120 => ['name' => 'power expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3121);
					$this->s->stay(3130);
				},
			]],
			3121 => ['name' => 'power expression op', 'transitions' => [
				T::arithmetic_op_power->name => function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3122);
					$this->s->move(3120);
				},
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],
			3122 => ['name' => 'power expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier('binaryPower'),
						$this->s->generated
					);
					$this->s->stay(3121);
				}
			]],


			3130 => ['name' => 'unary expression start', 'transitions' => [
				T::boolean_op_not->name => function(LT $token) {
					$this->s->result['op'] = 'unaryNot';
					$this->s->push(3131);
					$this->s->move(3130);
				},
				T::default_match->name => function(LT $token) {
					$this->s->result['op'] = 'unaryBitwiseNot';
					$this->s->push(3131);
					$this->s->move(3130);
				},
				T::arithmetic_op_plus->name => function(LT $token) {
					$this->s->result['op'] = 'unaryPlus';
					$this->s->push(3131);
					$this->s->move(3130);
				},
				T::arithmetic_op_minus->name => function(LT $token) {
					$this->s->result['op'] = 'unaryMinus';
					$this->s->push(3131);
					$this->s->move(3130);
				},
				'' => function(LT $token) {
					$this->s->stay(3140);
				}
			]],
			3131 => ['name' => 'unary expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated =
						$this->s->result['op'] === 'unaryNot' ?
							$this->nodeBuilder->booleanNot($this->s->generated) :
						$this->nodeBuilder->methodCall(
							$this->s->generated,
							new MethodNameIdentifier($this->s->result['op']),
							$this->nodeBuilder->constant(
								$this->nodeBuilder->nullValue
							)
						);
					$this->s->pop();
				}
			]],



			3140 => ['name' => 'postfix expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3141);
					$this->s->stay(3150);
				},
			]],
			3141 => ['name' => 'postfix expression op', 'transitions' => [
				T::property_accessor->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(3303);
				},
				T::pure_marker->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = true;
					$this->s->result['is_no_error'] = false;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(3305);
				},
				T::method_marker->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = false;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(3305);
				},
				T::lambda_return->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = true;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(3305);
				},
				T::error_as_external->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = true;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['method_name'] = 'errorAsExternal';
					$this->s->move(3306);
				},
				T::call_start->name => function(LT $token) {
					$this->s->push(3141);
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(3311);
				},
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(3141);
					$this->s->stay(3151);
				},
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::empty_set->name => $c,
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],

			3150 => ['name' => 'tuple call expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3151);
					$this->s->stay(3160);
				},
			]],
			3151 => ['name' => 'tuple call expression op', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->push(3152);
					$this->s->stay(3160);
				},
				T::empty_set->name => $c,
				T::empty_record->name => $c,
				T::empty_tuple->name => $c,
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			3152 => ['name' => 'tuple call expression op expr', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(3151);
				},
			]],

			3160 => ['name' => 'expression start', 'transitions' => [
				T::string_value->name => $c = function(LT $token) {
					$this->s->stay(202);
				},
				T::byte_array_value->name => $c,
				T::positive_integer_number->name => $c,
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::empty_set->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::lambda_param->name => $c,
				T::type->name => $c,
				T::type_short->name => $c,

				T::error_marker->name => function(LT $token) { $this->s->move(342); },
				T::mutable->name => function(LT $token) { $this->s->move(344); },
				T::val->name => function(LT $token) { $this->s->move(359); },

				T::call_start->name => -206,
				T::sequence_start->name => -204,
				T::sequence_end->name => function(LT $token) { $this->s->stay(318); },
				T::no_error->name => -208,
				T::no_external_error->name => -210,

				T::var->name => -613,

				T::var_keyword->name => function(LT $token) {
					$this->s->result = ['var_name' => $token->patternMatch->text];
					$this->s->move(212);
				},
				T::special_var->name => $tx = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->variableName(
						new VariableNameIdentifier($token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
				T::special_var_param->name => $tx,
				T::special_var_modulo->name => $tx,
				T::this_var->name => $tx,
				T::type_keyword->name => function(LT $token) {
					$this->s->result = ['type_name' => $token->patternMatch->text];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(216);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(222);
				},
				T::when_value_of->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchValue';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(319);
				},
				T::when_type_of->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchType';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(319);
				},
				T::when_is_true->name => function(LT $token) {
					$this->s->result['matchType'] = 'isTrue';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(323);
				},
				T::when_is_error->name => function(LT $token) {
					$this->s->result['matchType'] = 'isError';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(352);
				},
				T::when->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchIf';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(333);
				},
			]],





			3303 => ['name' => 'property name', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->propertyAccess(
						$this->s->generated,
						$this->stringEscapeCharHandler->unescape( $token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->propertyAccess(
						$this->s->generated,
						is_numeric($token->patternMatch->text) ?
							(int)$token->patternMatch->text :
							$token->patternMatch->text
					);
					$this->s->moveAndPop();
				},
				T::type->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::null->name => $c,
				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::error_as_external->name => $c,
				T::lambda_return->name => $c,
				T::positive_integer_number->name => $c,
			]],


			3305 => ['name' => 'method name', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['method_name'] = $token->patternMatch->text;
					$this->s->move(3306);
				},
				T::type_keyword->name => $c,
				T::type->name => $c,
				T::val->name => $c,
			]],
			3306 => ['name' => 'method name next', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->move(3307);
				},
				T::tuple_start->name => $t = function(LT $token) {
					$this->s->stay(3309);
				},
				T::empty_tuple->name => $t,
				T::empty_set->name => $t,
				T::empty_record->name => $t,
				'' => function(LT $token) {
					$this->noErrorMethodCall(false);
					$this->s->pop();
				},
			]],
			3307 => ['name' => 'method call start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3308);
					$this->s->stay(3000);
				}
			]],
			3308 => ['name' => 'method call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->noErrorMethodCall(true);
					$this->s->moveAndPop();
				}
			]],
			3309 => ['name' => 'method call start tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(3310);
					$this->s->stay(3160);
				}
			]],
			3310 => ['name' => 'method call value tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->noErrorMethodCall(true);
					$this->s->pop();
				}
			]],
			3311 => ['name' => 'function call start', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->nodeBuilder->constant($this->nodeBuilder->nullValue)
					);
					$this->s->moveAndPop();
				},
				'' => function(LT $token) {
					$this->s->push(3312);
					$this->s->stay(3000);
				}
			]],
			3312 => ['name' => 'function call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->moveAndPop();
				}
			]],
			318 => ['name' => 'sequence early end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->sequence([]);
					$this->s->pop();
				}
			]],
			319 => ['name' => 'match value of start', 'transitions' => [
				T::call_start->name => 320
			]],
			320 => ['name' => 'match value of target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(321);
					$this->s->stay(3000);
				}
			]],
			321 => ['name' => 'match value of target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(322);
				}
			]],
			322 => ['name' => 'match value is', 'transitions' => [
				T::when_value_is->name => 323
			]],
			323 => ['name' => 'match value of target end', 'transitions' => [
				T::sequence_start->name => 324
			]],
			324 => ['name' => 'match value pair start', 'transitions' => [
				T::default_match->name => 329,
				'' => function(LT $token) {
					$this->s->push(325);
					$this->s->stay(3000);
				}
			]],
			325 => ['name' => 'match value pair match return', 'transitions' => [
				T::colon->name => function(LT $token) {
					$this->s->result['matchPairMatch'] = $this->s->generated;
					$this->s->move(326);
				}
			]],
			326 => ['name' => 'match value pair value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(327);
					$this->s->stay(3000);
				}
			]],
			327 => ['name' => 'match value pair value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['matchPairs'][] = $this->nodeBuilder->matchPair(
						$this->s->result['matchPairMatch'],
						$this->s->generated
					);
					$this->s->stay(328);
				}
			]],
			328 => ['name' => 'match value pair separator', 'transitions' => [
				T::value_separator->name => 324,
				T::sequence_end->name => 332
			]],
			329 => ['name' => 'match value pair match return', 'transitions' => [
				T::colon->name => 330
			]],
			330 => ['name' => 'match value default pair start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(331);
					$this->s->stay(3000);
				}
			]],
			331 => ['name' => 'match value pair value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['matchPairs'][] = $this->nodeBuilder->matchDefault(
						$this->s->generated
					);
					$this->s->stay(328);
				}
			]],
			332 => ['name' => 'match value pair match return', 'transitions' => [
				'' => function(LT $token) {
					/** @phpstan-ignore-next-line match.unhandled */
					$this->s->generated = match($this->s->result['matchType']) {
						'isTrue' => $this->nodeBuilder->matchTrue(
							$this->s->result['matchPairs']
						),
						'matchType' => $this->nodeBuilder->matchType(
							$this->s->result['matchTarget'],
							$this->s->result['matchPairs']
						),
						'matchValue' => $this->nodeBuilder->matchValue(
							$this->s->result['matchTarget'],
							$this->s->result['matchPairs']
						),
					};
					$this->s->pop();
				}
			]],
			333 => ['name' => 'match if start', 'transitions' => [
				T::call_start->name => 334
			]],
			334 => ['name' => 'match if target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(335);
					$this->s->stay(3000);
				}
			]],
			335 => ['name' => 'match if target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(336);
				}
			]],
			336 => ['name' => 'match if then start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(337);
					$this->s->stay(3000);
				}
			]],
			337 => ['name' => 'match if else check', 'transitions' => [
				T::default_match->name => function(LT $token) {
					$this->s->result['matchThen'] = $this->s->generated;
					$this->s->move(338);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchIf(
						$this->s->result['matchTarget'],
						$this->s->generated,
						$this->nodeBuilder->constant(
							$this->nodeBuilder->nullValue
						)
					);
					$this->s->pop();
				}
			]],
			338 => ['name' => 'match if else start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(339);
					$this->s->stay(3000);
				}
			]],
			339 => ['name' => 'match if else check', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchIf(
						$this->s->result['matchTarget'],
						$this->s->result['matchThen'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			342 => ['name' => 'error value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(343);
					$this->s->stay(3160);
				},
			]],
			343 => ['name' => 'error value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier('Error'),
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			344 => ['name' => 'mutable value', 'transitions' => [
				T::sequence_start->name => 345,
			]],
			345 => ['name' => 'mutable value type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(346);
					$this->s->stay(4000);
				},
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			346 => ['name' => 'mutable value type separator', 'transitions' => [
				T::value_separator->name => 347,
			]],
			347 => ['name' => 'mutable value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['mutable_type'] = $this->s->generated;
					$this->s->push(348);
					$this->s->stay(3000);
				},
			]],
			348 => ['name' => 'mutable value type return', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutable(
						$this->s->result['mutable_type'],
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			352 => ['name' => 'match error start', 'transitions' => [
				T::call_start->name => 353
			]],
			353 => ['name' => 'match error target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(354);
					$this->s->stay(3000);
				}
			]],
			354 => ['name' => 'match error target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(355);
				}
			]],
			355 => ['name' => 'match error then start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(356);
					$this->s->stay(3000);
				}
			]],
			356 => ['name' => 'match error else check', 'transitions' => [
				T::default_match->name => function(LT $token) {
					$this->s->result['matchThen'] = $this->s->generated;
					$this->s->move(357);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchError(
						$this->s->result['matchTarget'],
						$this->s->generated,
						null
					);
					$this->s->pop();
				}
			]],
			357 => ['name' => 'match error start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(358);
					$this->s->stay(3000);
				}
			]],
			358 => ['name' => 'match error else check', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchError(
						$this->s->result['matchTarget'],
						$this->s->result['matchThen'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			359 => ['name' => 'constant value start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(360);
					$this->s->move(401);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->push(361);
					$this->s->stay(401);
				},
			]],
			360 => ['name' => 'constant value end', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			361 => ['name' => 'constant value tuple end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			401 => ['name' => 'value start', 'transitions' => [
				T::string_value->name => function(LT $token) { $this->s->stay(408); },
				T::byte_array_value->name => function(LT $token) { $this->s->stay(4081); },
				T::positive_integer_number->name => function(LT $token) { $this->s->stay(416); },
				T::integer_number->name => function(LT $token) { $this->s->stay(416); },
				T::real_number->name => function(LT $token) { $this->s->stay(417); },
				T::empty_tuple->name => function(LT $token) { $this->s->stay(402); },
				T::empty_record->name => function(LT $token) { $this->s->stay(403); },
				T::empty_set->name => function(LT $token) { $this->s->stay(404); },
				T::null->name => function(LT $token) { $this->s->stay(405); },
				T::true->name => function(LT $token) { $this->s->stay(406); },
				T::false->name => function(LT $token) { $this->s->stay(407); },
				T::lambda_param->name => function(LT $token) { $this->s->stay(418); },
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(420);
				},
				T::type->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(440);
				},
				T::type_short->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(439);
				},
				T::error_marker->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(409);
					},
				T::mutable->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(411);
				},
				T::type_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['current_type_name'] = $token->patternMatch->text;
					$this->s->move(444);
				},
			]],
			402 => ['name' => 'empty list value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->generated = $this->nodeBuilder->tupleValue([]);
					$this->s->moveAndPop();
				},
			]],
			403 => ['name' => 'empty dict value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordValue([]);
					$this->s->moveAndPop();
				},
			]],
			404 => ['name' => 'empty set value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setValue([]);
					$this->s->moveAndPop();
				},
			]],
			405 => ['name' => 'null value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->nullValue;
					$this->s->moveAndPop();
				},
			]],
			406 => ['name' => 'true value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->trueValue;
					$this->s->moveAndPop();
				},
			]],
			407 => ['name' => 'false value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->falseValue;
					$this->s->moveAndPop();
				},
			]],
			408 => ['name' => 'string value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->stringValue(
						$this->stringEscapeCharHandler->unescape( $token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
			]],
			4081 => ['name' => 'byte array value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->bytesValue(
						$this->bytesEscapeCharHandler->unescape( $token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
			]],
			409 => ['name' => 'error constant value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(410);
					$this->s->stay(401);
				},
			]],
			410 => ['name' => 'error constant value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->errorValue(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			411 => ['name' => 'mutable constant value', 'transitions' => [
				T::sequence_start->name => 412,
			]],
			412 => ['name' => 'mutable constant value type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(413);
					$this->s->stay(4000);
				},
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			413 => ['name' => 'mutable constant value type separator', 'transitions' => [
				T::value_separator->name => 414,
			]],
			414 => ['name' => 'mutable constant value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['mutable_type'] = $this->s->generated;
					$this->s->push(415);
					$this->s->stay(401);
				},
			]],
			415 => ['name' => 'mutable constant value type return', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableValue(
						$this->s->result['mutable_type'],
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			416 => ['name' => 'integer value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerValue(new Number($token->patternMatch->text));
					$this->s->moveAndPop();
				},
			]],
			417 => ['name' => 'real value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realValue(new Number($token->patternMatch->text));
					$this->s->moveAndPop();
				},
			]],
			418 => ['name' => 'function value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(419);
					$this->s->stay(501);
				},
			]],
			419 => ['name' => 'function value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			420 => ['name' => 'list or dict value', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->stringEscapeCharHandler->unescape( $token->patternMatch->text)
						),
						$token->sourcePosition
					);
					$this->s->move(421);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(421);
				},
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				'' => function(LT $token) {
					$this->s->stay(427);
				},
			]],
			421 => ['name' => 'dict value separator', 'transitions' => [
				T::colon->name => 422,
				'' => function(LT $token) {
					$this->s->back(427);
				}
			]],
			422 => ['name' => 'dict value value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['current_key'] ??= $this->s->result['first_token']->patternMatch->text;
					$this->s->push(423);
					$this->s->stay(401);
				},
			]],
			423 => ['name' => 'dict value dict value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(424);
				}
			]],
			424 => ['name' => 'dict value dict value separator', 'transitions' => [
				T::tuple_end->name => 425,
				T::value_separator->name => 426,
			]],
			425 => ['name' => 'dict value dict value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			426 => ['name' => 'dict value dict value key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->stringEscapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(421);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(421);
				},
				T::val->name => $c,
				T::type_keyword->name => $c,
				T::mutable->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
			]],
			427 => ['name' => 'list or set value value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(428);
					$this->s->stay(401);
				},
			]],
			428 => ['name' => 'list or set value list value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(429);
				}
			]],
			429 => ['name' => 'list or set value list value separator', 'transitions' => [
				T::tuple_end->name => 434,
				T::value_separator->name => 431,
				T::expression_separator->name => 430,
			]],
			430 => ['name' => 'list value set value', 'transitions' => [
				T::tuple_end->name => 438,
				'' => function(LT $token) {
					$this->s->stay(435);
				},
			]],
			431 => ['name' => 'list value tuple value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(432);
					$this->s->stay(401);
				},
			]],
			432 => ['name' => 'list value list value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(433);
				}
			]],
			433 => ['name' => 'list value list value separator', 'transitions' => [
				T::tuple_end->name => 434,
				T::value_separator->name => 431,
			]],
			434 => ['name' => 'list value list value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			435 => ['name' => 'list value set value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(436);
					$this->s->stay(401);
				},
			]],
			436 => ['name' => 'list value set value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(437);
				}
			]],
			437 => ['name' => 'list value set value separator', 'transitions' => [
				T::tuple_end->name => 438,
				T::expression_separator->name => 435,
			]],
			438 => ['name' => 'list value set value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],

			4381 => ['name' => 'type value short optional key', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType(
						$this->s->generated
					);
					$this->s->stay(443);
				}
			]],
			4382 => ['name' => 'type value short type optional key', 'transitions' => [
				T::type_start->name => 4383,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType($this->nodeBuilder->anyType);
					$this->s->stay(443);
				},
			]],
			4383 => ['name' => 'type value short type optional type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(4384);
					$this->s->stay(4000);
				},
			]],
			4384 => ['name' => 'type value short type optional return point', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->move(443);
				}
			]],

			439 => ['name' => 'type value short', 'transitions' => [
				T::optional_key->name => function(LT $token) {
					$this->s->push(4381);
					$this->s->move(4000);
				},
				T::type_keyword->name => $c = function(LT $token) {
					if ($token->rule->tag === T::type_keyword->name && $token->patternMatch->text === 'OptionalKey') {
						$this->s->move(4382);
						return;
					}
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'IntegerSubset', 'MutableValue',
						'RealSubset', 'StringSubset', 'State', 'Subset', 'Alias', 'Named'
					], true)) {
						$this->s->generated = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(443);
						return;
					}
					$this->s->push(443);
					$this->s->stay(4000);
				},
				T::call_start->name => $c,
				T::sequence_start->name => $c, 
				T::arithmetic_op_multiply->name => $c,
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
				T::empty_tuple->name => $c,
				T::empty_set->name => $c,
				T::empty_record->name => $c,
			]],
			440 => ['name' => 'type value', 'transitions' => [
				T::tuple_start->name => function(LT $token) {
					$this->s->push(443);
					$this->s->stay(4000);
				},
				T::sequence_start->name => 441,
			]],

			4401 => ['name' => 'type value type optional key', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType(
						$this->s->generated
					);
					$this->s->stay(442);
				}
			]],
			4402 => ['name' => 'type value type type optional key', 'transitions' => [
				T::type_start->name => 4403,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType($this->nodeBuilder->anyType);
					$this->s->stay(442);
				},
			]],
			4403 => ['name' => 'type value type type optional type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(4404);
					$this->s->stay(4000);
				},
			]],
			4404 => ['name' => 'type value type optional return point', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->move(442);
				}
			]],


			441 => ['name' => 'type value type', 'transitions' => [
				T::optional_key->name => function(LT $token) {
					$this->s->push(4401);
					$this->s->move(4000);
				},
				T::type_keyword->name => $c = function(LT $token) {
					if ($token->rule->tag === T::type_keyword->name && $token->patternMatch->text === 'OptionalKey') {
						$this->s->move(4402);
						return;
					}
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'IntegerSubset', 'MutableValue',
						'RealSubset', 'StringSubset', 'State', 'Subset', 'Alias', 'Named'
					], true)) {
						$this->s->generated = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(442);
						return;
					}
					$this->s->push(442);
					$this->s->stay(4000);
				},
				T::call_start->name => $c,
				T::sequence_start->name => $c, 
				T::arithmetic_op_multiply->name => $c,
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			442 => ['name' => 'type value type separator', 'transitions' => [
				T::sequence_end->name => 443,
			]],
			443 => ['name' => 'type value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeValue($this->s->generated);
					$this->s->pop();
				},
			]],
			444 => ['name' => 'value type name', 'transitions' => [
				T::property_accessor->name => 445,
				T::boolean_op_not->name => function (LT $token) {
					$this->s->push(446);
					$this->s->move(401);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->atomValue(
						new TypeNameIdentifier($this->s->result['current_type_name']),
					);
					$this->s->pop();
				},
			]],
			445 => ['name' => 'value enum', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->enumerationValue(
						new TypeNameIdentifier($this->s->result['current_type_name']),
						new EnumValueIdentifier($token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
				T::var_keyword->name => $c,
			]],
			446 => ['name' => 'value data return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->dataValue(
						new TypeNameIdentifier($this->s->result['current_type_name']),
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			501 => ['name' => 'function value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(502);
					$this->s->stay(601);
				},
			]],
			502 => ['name' => 'function value parameter return 1', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter'] = $this->s->generated['parameter_type'];
					$this->s->result['return'] = $this->s->generated['return_type'];
					$this->s->stay(503);
				},
			]],
			503 => ['name' => 'function value parameter return 2', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(506);
					$this->s->move(625);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->move(504);
				}
			]],
			504 => ['name' => 'function value body return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(505);
					$this->s->stay(2000);
				},
			]],
			505 => ['name' => 'function value body return', 'transitions' => [
				'' => function(LT $token) {
					$return = $this->s->generated;
					$this->s->generated = $this->nodeBuilder->functionValue(
						$this->nodeBuilder->nameAndType(
							$this->s->result['parameter'] ?? $this->nodeBuilder->anyType,
							$this->s->result['parameter_name'] ?? null
						),
						$this->nodeBuilder->nameAndType(
							$this->s->result['dependency'] ?? $this->nodeBuilder->nothingType,
							$this->s->result['dependency_name'] ?? null
						),
						$this->s->result['return'] ?? $this->nodeBuilder->anyType,
						$this->nodeBuilder->functionBody($return)
					);
					$this->s->pop();
				}
			]],
			506 => ['name' => 'function value dependency type', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->move(504);
				}
			]],
			601 => ['name' => 'function value type', 'transitions' => [
				T::lambda_param->name => function(LT $token) {
					$this->s->push(603);
					$this->s->move(605);
				},
			]],
			602 => ['name' => 'method type', 'transitions' => [
				T::lambda_param->name => function(LT $token) {
					$this->s->push(603);
					$this->s->move(605);
				},
				T::lambda_return->name => $c = function(LT $token) {
					$this->s->result['parameter_name'] = null;
					$this->s->result['parameter_type'] = $this->nodeBuilder->nullType;
					$this->s->push(604);
					$this->s->stay(611);
				},
				T::call_end->name => $c,
			]],
			603 => ['name' => 'function or method value type parameter', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->s->generated['parameter_type'];
					$this->s->push(604);
					$this->s->stay(611);
				},
			]],
			604 => ['name' => 'function or method value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = [
						'parameter_name' => $this->s->result['parameter_name'],
						'parameter_type' => $this->s->result['parameter_type'],
						'return_type' => $this->s->generated
					];
					$this->s->pop();
				},
			]],
			605 => ['name' => 'function value parameter type', 'transitions' => [
				T::var_keyword->name => $d = function(LT $token) {
					$this->s->result['parameter_name'] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(606);
				},
				T::default_match->name => function(LT $token) {
					$this->s->move(608);
				},
				T::arithmetic_op_multiply->name => $c = function(LT $token) {
					$this->s->result['parameter_name'] = null;
					$this->s->push(609);
					$this->s->stay(4000);
				},
				T::type_proxy_keyword->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->nullType;
					$this->s->stay(610);
				},
			]],
			606 => ['name' => 'function value parameter name', 'transitions' => [
				T::colon->name => 607,
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->anyType;
					$this->s->stay(610);
				}
			]],
			607 => ['name' => 'function value parameter type', 'transitions' => [
				T::arithmetic_op_multiply->name => $c = function(LT $token) {
					$this->s->push(609);
					$this->s->stay(4000);
				},
				T::type_proxy_keyword->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,
			]],
			608 => ['name' => 'function value parameter name from type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['parameter_name'] = new VariableNameIdentifier(
						lcfirst($token->patternMatch->text)
					);
					$this->s->push(609);
					$this->s->stay(4000);
				},
			]],
			609 => ['name' => 'function value parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->s->generated;
					$this->s->stay(610);
				},
			]],
			610 => ['name' => 'function value parameter exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = [
						'parameter_name' => $this->s->result['parameter_name'] ?? null,
						'parameter_type' => $this->s->result['parameter_type']
					];
					$this->s->pop();
				},
			]],
			611 => ['name' => 'function or method value return', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->push(612);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->anyType;
					$this->s->pop();
				},
			]],
			612 => ['name' => 'function or method value return exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			613 => ['name' => 'var start', 'transitions' => [
				T::sequence_start->name => 614
			]],
			614 => ['name' => 'var list or dict', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['first_variable_name'] = $token->patternMatch->text;
					$this->s->move(615);
				},
				T::default_match->name => function(LT $token) {
					$this->s->result['variables'] = [];
					$this->s->move(620);
				},
				T::string_value->name => function(LT $token) {
					$this->s->result['next_variable_key'] = $this->stringEscapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(623);
				},
				T::null->name => $c = function(LT $token) {
					$this->s->result['next_variable_key'] = $token->patternMatch->text;
					$this->s->move(623);
				},
				T::true->name => $c,
				T::false->name => $c,
				T::when_value_is->name => $c,
				T::var->name => $c,
				T::val->name => $c,
				T::mutable->name => $c,
				T::type->name => $c,
			]],
			615 => ['name' => 'var list separator', 'transitions' => [
				T::value_separator->name => function(LT $token) {
					$this->s->result['variables'] = [new VariableNameIdentifier($this->s->result['first_variable_name'])];
					$this->s->move(617);
				},
				T::colon->name => function(LT $token) {
					$this->s->result['next_variable_key'] = $this->s->result['first_variable_name'];
					$this->s->move(624);
				},
				T::sequence_end->name => function(LT $token) {
					$this->s->result['variables'] = [new VariableNameIdentifier($this->s->result['first_variable_name'])];
					$this->s->move(618);
				},
			]],
			616 => ['name' => 'var list separator', 'transitions' => [
				T::value_separator->name => 617,
				T::sequence_end->name => 618,
			]],
			617 => ['name' => 'var list next', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(616);
				},
			]],
			618 => ['name' => 'var list assign', 'transitions' => [
				T::assign->name => function(LT $token) {
					$this->s->push(619);
					$this->s->move(3000);
				},
			]],
			619 => ['name' => 'var list end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->multiVariableAssignment(
						$this->s->result['variables'],
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			620 => ['name' => 'var list assign', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][$token->patternMatch->text] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(621);
				},
			]],
			621 => ['name' => 'var dict separator', 'transitions' => [
				T::value_separator->name => 622,
				T::sequence_end->name => 618,
			]],
			622 => ['name' => 'var dict next', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['next_variable_key'] = $token->patternMatch->text;
					$this->s->move(623);
				},
				T::true->name => $c,
				T::false->name => $c,
				T::null->name => $c,
				T::type->name => $c,
				T::var->name => $c,
				T::val->name => $c,
				T::mutable->name => $c,
				T::when_value_is->name => $c,
				T::string_value->name => function(LT $token) {
					$this->s->result['next_variable_key'] = $this->stringEscapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(623);
				},
				T::default_match->name => 620,
			]],
			623 => ['name' => 'var dict colon', 'transitions' => [
				T::colon->name => 624,
			]],
			624 => ['name' => 'var dict key value', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][$this->s->result['next_variable_key']] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(621);
				},
			]],
			625 => ['name' => 'dependency parameter start', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['dependency_name'] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(626);
				},
				T::default_match->name => function(LT $token) {
					$this->s->move(628);
				},
				'' => function(LT $token) {
					$this->s->result['dependency_name'] = null;
					$this->s->push(629);
					$this->s->stay(4000);
				},
			]],
			626 => ['name' => 'dependency parameter name', 'transitions' => [
				T::colon->name => 627,
			]],
			627 => ['name' => 'dependency parameter name type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(629);
					$this->s->stay(4000);
				},
			]],
			628 => ['name' => 'dependency parameter name from type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['dependency_name'] = new VariableNameIdentifier(
						lcfirst($token->patternMatch->text)
					);
					$this->s->push(629);
					$this->s->stay(4000);
				},
			]],
			629 => ['name' => 'function value parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated;
					$this->s->stay(630);
				},
			]],
			630 => ['name' => 'function value parameter exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = [
						'dependency_name' => $this->s->result['dependency_name'] ?? null,
						'dependency_type' => $this->s->result['dependency_type']
					];
					$this->s->pop();
				},
			]],
			706 => ['name' => 'type basic', 'transitions' => [
				'' => function(LT $token) {
					/** @phpstan-ignore-next-line match.unhandled */
					$this->s->generated = match($this->s->result['typeName']) {
						'Any' => $this->nodeBuilder->anyType,
						'Nothing' => $this->nodeBuilder->nothingType,
						'Boolean' => $this->nodeBuilder->booleanType,
						'True' => $this->nodeBuilder->trueType,
						'False' => $this->nodeBuilder->falseType,
						'Null' => $this->nodeBuilder->nullType,
						/*
						'String' => $this->nodeBuilder->stringType(),
						'Integer' => $this->nodeBuilder->integerType(),
						'Real' => $this->nodeBuilder->realType(),
						'Array' => $this->nodeBuilder->arrayType(),
						'Set' => $this->nodeBuilder->setType(),
						'Map' => $this->nodeBuilder->mapType(),
						*/
						'MutableValue' => $this->nodeBuilder->metaTypeType(MetaTypeValue::MutableValue),
						'Enumeration' => $this->nodeBuilder->metaTypeType(MetaTypeValue::Enumeration),
					};
					$this->s->pop();
				},
			]],
			709 => ['name' => 'type integer', 'transitions' => [
				T::type_start->name => 710,
				T::tuple_start->name => 715,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerType();
					$this->s->pop();
				},
			]],
			710 => ['name' => 'type integer range start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['minValue'] = $token->patternMatch->text;
					$this->s->move(711);
				},
				T::integer_number->name => $c,
				T::range_dots->name => 712,
				T::call_start->name => $f = function(LT $token) {
					$this->s->push(717);
					$this->s->stay(870);
				},
				T::tuple_start->name => $f
			]],
			711 => ['name' => 'type integer range dots', 'transitions' => [
				T::range_dots->name => 712,
				'' => function(LT $token) {
					$value = $this->s->result['minValue'];
					$this->s->push(717);
					$this->s->result['intervals'] = [
						$this->nodeBuilder->numberInterval(
							new NumberIntervalEndpoint(
								new Number($value),
								true,
							),
							new NumberIntervalEndpoint(
								new Number($value),
								true,
							),
						)
					];
					$this->s->stay(878);
				}
			]],
			712 => ['name' => 'type integer range end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['maxValue'] = $token->patternMatch->text;
					$this->s->move(713);
				},
				T::integer_number->name => $c,
				T::type_end->name => 714
			]],
			713 => ['name' => 'type integer type end', 'transitions' => [
				T::type_end->name => 714
			]],
			714 => ['name' => 'type integer return', 'transitions' => [
				'' => function(LT $token) {
					if (isset($this->s->result['subsetValues'])) {
						$this->s->generated = $this->nodeBuilder->integerSubsetType(
							$this->s->result['subsetValues']
						);
					} else {
						$this->s->generated = $this->nodeBuilder->integerType(
							isset($this->s->result['minValue']) ? new Number($this->s->result['minValue']) : MinusInfinity::value,
							isset($this->s->result['maxValue']) ? new Number($this->s->result['maxValue']) : PlusInfinity::value
						);
					}
					$this->s->pop();
				},
			]],
			715 => ['name' => 'type integer subset value', 'transitions' => [
				T::integer_number->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new Number($token->patternMatch->text);
					$this->s->move(716);
				},
				T::positive_integer_number->name => $c
			]],
			716 => ['name' => 'type integer subset separator', 'transitions' => [
				T::value_separator->name => 715,
				T::tuple_end->name => 714
			]],
			717 => ['name' => 'type integer full return', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerFullType(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			718 => ['name' => 'type real', 'transitions' => [
				T::type_start->name => 719,
				T::tuple_start->name => 724,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realType();
					$this->s->pop();
				},
			]],
			719 => ['name' => 'type real range start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['minValue'] = $token->patternMatch->text;
					$this->s->move(720);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::range_dots->name => 721,
				T::call_start->name => $f = function(LT $token) {
					$this->s->push(726);
					$this->s->stay(880);
				},
				T::tuple_start->name => $f
			]],
			720 => ['name' => 'type real range dots', 'transitions' => [
				T::range_dots->name => 721,
				'' => function(LT $token) {
					$value = $this->s->result['minValue'];
					$this->s->push(726);
					$this->s->result['intervals'] = [
						$this->nodeBuilder->numberInterval(
							new NumberIntervalEndpoint(
								new Number($value),
								true,
							),
							new NumberIntervalEndpoint(
								new Number($value),
								true,
							),
						)
					];
					$this->s->stay(888);
				}
			]],
			721 => ['name' => 'type real range end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['maxValue'] = $token->patternMatch->text;
					$this->s->move(722);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::type_end->name => 723
			]],
			722 => ['name' => 'type real type end', 'transitions' => [
				T::type_end->name => 723
			]],
			723 => ['name' => 'type real return', 'transitions' => [
				'' => function(LT $token) {
					if (isset($this->s->result['subsetValues'])) {
						$this->s->generated = $this->nodeBuilder->realSubsetType(
							$this->s->result['subsetValues']
						);
					} else {
						$this->s->generated = $this->nodeBuilder->realType(
							isset($this->s->result['minValue']) ? new Number($this->s->result['minValue']) : MinusInfinity::value,
							isset($this->s->result['maxValue']) ? new Number($this->s->result['maxValue']) : PlusInfinity::value
						);
					}
					$this->s->pop();
				},
			]],
			724 => ['name' => 'type real subset value', 'transitions' => [
				T::real_number->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new Number($token->patternMatch->text);
					$this->s->move(725);
				},
				T::integer_number->name => $c,
				T::positive_integer_number->name => $c
			]],
			725 => ['name' => 'type real subset separator', 'transitions' => [
				T::value_separator->name => 724,
				T::tuple_end->name => 723
			]],
			726 => ['name' => 'type real full return', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realFullType(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],

			7271 => ['name' => 'type byte array', 'transitions' => [
				T::type_start->name => 7272,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->bytesType();
					$this->s->pop();
				},
			]],
			7272 => ['name' => 'type byte array range start', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(7273);
				},
				T::range_dots->name => 7274
			]],
			7273 => ['name' => 'type byte array range dots', 'transitions' => [
				T::range_dots->name => 7274,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(7276);
				}
			]],
			7274 => ['name' => 'type string range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(7275);
				},
				T::type_end->name => 7276
			]],
			7275 => ['name' => 'type string type end', 'transitions' => [
				T::type_end->name => 7276
			]],
			7276 => ['name' => 'type string return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->bytesType(
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],

			727 => ['name' => 'type string', 'transitions' => [
				T::type_start->name => 728,
				T::tuple_start->name => 733,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->stringType();
					$this->s->pop();
				},
			]],
			728 => ['name' => 'type string range start', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(729);
				},
				T::range_dots->name => 730
			]],
			729 => ['name' => 'type string range dots', 'transitions' => [
				T::range_dots->name => 730,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(732);
				}
			]],
			730 => ['name' => 'type string range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(731);
				},
				T::type_end->name => 732
			]],
			731 => ['name' => 'type string type end', 'transitions' => [
				T::type_end->name => 732
			]],
			732 => ['name' => 'type string return', 'transitions' => [
				'' => function(LT $token) {
					if (isset($this->s->result['subsetValues'])) {
						$this->s->generated = $this->nodeBuilder->stringSubsetType(
							$this->s->result['subsetValues']
						);
					} else {
						$this->s->generated = $this->nodeBuilder->stringType(
							isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
							isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
						);
					}
					$this->s->pop();
				},
			]],
			733 => ['name' => 'type string subset value', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = $this->stringEscapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(734);
				},
			]],
			734 => ['name' => 'type string subset separator', 'transitions' => [
				T::value_separator->name => 733,
				T::tuple_end->name => 732
			]],
			735 => ['name' => 'type array', 'transitions' => [
				T::type_start->name => 736,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->arrayType();
					$this->s->pop();
				},
			]],
			736 => ['name' => 'type array type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(737);
				},
				T::range_dots->name => 739,
				'' => function(LT $token) {
					$this->s->push(742);
					$this->s->stay(4000);
				},
			]],
			737 => ['name' => 'type array range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(738);
				},
			]],
			738 => ['name' => 'type array range dots', 'transitions' => [
				T::range_dots->name => 739,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(741);
				}
			]],
			739 => ['name' => 'type array range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(740);
				},
				T::type_end->name => 741
			]],
			740 => ['name' => 'type array type end', 'transitions' => [
				T::type_end->name => 741
			]],
			741 => ['name' => 'type array return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->arrayType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			742 => ['name' => 'type array return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(743);
				}
			]],
			743 => ['name' => 'type array separator', 'transitions' => [
				T::value_separator->name => 744,
				T::type_end->name => 741
			]],
			744 => ['name' => 'type array type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(737);
				},
				T::range_dots->name => 739,
			]],
			745 => ['name' => 'type map', 'transitions' => [
				T::type_start->name => 746,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mapType();
					$this->s->pop();
				},
			]],
			746 => ['name' => 'type map type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(747);
				},
				T::range_dots->name => 749,
				'' => function(LT $token) {
					$this->s->push(752);
					$this->s->stay(4000);
				},
			]],
			747 => ['name' => 'type map range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(748);
				}
			]],
			748 => ['name' => 'type map range dots', 'transitions' => [
				T::range_dots->name => 749,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(751);
				}
			]],
			749 => ['name' => 'type map range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(750);
				},
				T::type_end->name => 751
			]],
			750 => ['name' => 'type map type end', 'transitions' => [
				T::type_end->name => 751
			]],
			751 => ['name' => 'type map return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mapType(
						$this->s->result['keyType'] ?? null,
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			752 => ['name' => 'type map return point key or item type', 'transitions' => [
				T::colon->name => function(LT $token) {
					$this->s->result['keyType'] = $this->s->generated;
					$this->s->push(753);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(754);
				}
			]],
			753 => ['name' => 'type map return point item type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(754);
				}
			]],
			754 => ['name' => 'type map separator', 'transitions' => [
				T::value_separator->name => 755,
				T::type_end->name => 751
			]],
			755 => ['name' => 'type map type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(747);
				},
				T::range_dots->name => 749,
			]],
			756 => ['name' => 'type type', 'transitions' => [
				T::type_start->name => 757,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],

			7561 => ['name' => 'type type optional key', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->nodeBuilder->optionalKeyType(
						$this->s->generated
					);
					$this->s->stay(759);
				}
			]],
			7562 => ['name' => 'type type optional key', 'transitions' => [
				T::type_start->name => 7563,
				'' => function(LT $token) {
					$this->s->result['type'] = $this->nodeBuilder->optionalKeyType($this->nodeBuilder->anyType);
					$this->s->stay(759);
				},
			]],
			7563 => ['name' => 'type type optional type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(7564);
					$this->s->stay(4000);
				},
			]],
			7564 => ['name' => 'type type optional return point', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->result['type'] = $this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->move(759);
				}
			]],

			757 => ['name' => 'type type type', 'transitions' => [
				T::optional_key->name => function(LT $token) {
					$this->s->push(7561);
					$this->s->move(4000);
				},
				T::type_keyword->name => function(LT $token) {
					if ($token->rule->tag === T::type_keyword->name && $token->patternMatch->text === 'OptionalKey') {
						$this->s->move(7562);
						return;
					}
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'IntegerSubset', 'RealSubset', 'StringSubset',
						'Data', 'Open', 'Sealed', 'Alias', 'Named', 'MutableValue',
					], true)) {
						$this->s->result['type'] = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(759);
						return;
					}
					$this->s->push(758);
					$this->s->stay(4000);
				},
				'' => function(LT $token) {
					$this->s->push(758);
					$this->s->stay(4000);
				},
			]],
			758 => ['name' => 'type type return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(759);
				}
			]],
			759 => ['name' => 'type type separator', 'transitions' => [
				T::type_end->name => 760
			]],
			760 => ['name' => 'type type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			761 => ['name' => 'type impure', 'transitions' => [
				T::type_start->name => 762,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->impureType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			762 => ['name' => 'type impure type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(763);
					$this->s->stay(4000);
				},
			]],
			763 => ['name' => 'type impure return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(764);
				}
			]],
			764 => ['name' => 'type impure separator', 'transitions' => [
				T::type_end->name => 765
			]],
			765 => ['name' => 'type impure return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->impureType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			766 => ['name' => 'type mutable', 'transitions' => [
				T::type_start->name => 767,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			767 => ['name' => 'type mutable type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(768);
					$this->s->stay(4000);
				},
			]],
			768 => ['name' => 'type mutable return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(769);
				}
			]],
			769 => ['name' => 'type mutable separator', 'transitions' => [
				T::type_end->name => 770
			]],
			770 => ['name' => 'type mutable return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			771 => ['name' => 'type error', 'transitions' => [
				T::type_start->name => 772,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->nothingType,
						$this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			772 => ['name' => 'type error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(773);
					$this->s->stay(4000);
				},
			]],
			773 => ['name' => 'type error return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(774);
				}
			]],
			774 => ['name' => 'type error separator', 'transitions' => [
				T::type_end->name => 775
			]],
			775 => ['name' => 'type error return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->nothingType,
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			776 => ['name' => 'type result', 'transitions' => [
				T::type_start->name => 777,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->anyType,
						$this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			777 => ['name' => 'type result type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(778);
					$this->s->stay(4000);
				},
			]],
			778 => ['name' => 'type result return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(779);
				}
			]],
			779 => ['name' => 'type result separator', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->result['error_type'] = $this->nodeBuilder->anyType;
					$this->s->stay(782);
				},
				T::value_separator->name => 780
			]],
			780 => ['name' => 'type result error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(781);
					$this->s->stay(4000);
				},
			]],
			781 => ['name' => 'type result error return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(782);
				}
			]],
			782 => ['name' => 'type result separator', 'transitions' => [
				T::type_end->name => 783
			]],
			783 => ['name' => 'type result return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						$this->s->result['error_type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			784 => ['name' => 'type proxy basic', 'transitions' => [
				T::tuple_start->name => 786,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->proxyType(
						new TypeNameIdentifier($this->s->result['typeName'])
					);
					$this->s->pop();
				},
			]],
			785 => ['name' => 'type basic', 'transitions' => [
				T::tuple_start->name => 786,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->namedType(
						new TypeNameIdentifier($this->s->result['typeName'])
					);
					$this->s->pop();
				},
			]],
			786 => ['name' => 'type enum subset value', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new EnumValueIdentifier($token->patternMatch->text);
					$this->s->move(787);
				},
				T::var_keyword->name => $c
			]],
			787 => ['name' => 'type string subset separator', 'transitions' => [
				T::value_separator->name => 786,
				T::tuple_end->name => 788
			]],
			788 => ['name' => 'type string return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->enumerationSubsetType(
						new TypeNameIdentifier($this->s->result['typeName']),
						$this->s->result['subsetValues']
					);
					$this->s->pop();
				},
			]],
			789 => ['name' => 'type optional key', 'transitions' => [
				T::type_start->name => 790,
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType(
							$this->nodeBuilder->anyType
						);
					$this->s->stay(844);
				},
			]],
			790 => ['name' => 'type optional type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(791);
					$this->s->stay(4000);
				},
			]],
			791 => ['name' => 'type optional return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(792);
				}
			]],
			792 => ['name' => 'type optional separator', 'transitions' => [
				T::type_end->name => 793
			]],
			793 => ['name' => 'type optional return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->stay(844);
				},
			]],
			794 => ['name' => 'type optional ? return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->stay(844);
				}
			]],
			820 => ['name' => 'type shape', 'transitions' => [
				T::type_start->name => 821,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->shapeType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			821 => ['name' => 'type shape type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(822);
					$this->s->stay(4000);
				},
			]],
			822 => ['name' => 'type shape return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(823);
				}
			]],
			823 => ['name' => 'type shape separator', 'transitions' => [
				T::type_end->name => 824
			]],
			824 => ['name' => 'type shape return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->shapeType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			827 => ['name' => 'type set', 'transitions' => [
				T::type_start->name => 828,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setType();
					$this->s->pop();
				},
			]],
			828 => ['name' => 'type set type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(829);
				},
				T::range_dots->name => 831,
				'' => function(LT $token) {
					$this->s->push(834);
					$this->s->stay(4000);
				},
			]],
			829 => ['name' => 'type set range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(830);
				},
			]],
			830 => ['name' => 'type set range dots', 'transitions' => [
				T::range_dots->name => 831,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(833);
				}
			]],
			831 => ['name' => 'type set range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(832);
				},
				T::type_end->name => 833
			]],
			832 => ['name' => 'type set type end', 'transitions' => [
				T::type_end->name => 833
			]],
			833 => ['name' => 'type set return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			834 => ['name' => 'type set return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(835);
				}
			]],
			835 => ['name' => 'type set separator', 'transitions' => [
				T::value_separator->name => 836,
				T::type_end->name => 833
			]],
			836 => ['name' => 'type set type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(829);
				},
				T::range_dots->name => 831,
			]],
			837 => ['name' => 'module level empty tuple', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->nodeBuilder->tupleType([])
						)
					);
					$this->s->pop();
				}
			]],
			838 => ['name' => 'module level empty record', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->nodeBuilder->recordType([])
						)
					);
					$this->s->pop();
				}
			]],
			839 => ['name' => 'module level tuple or record', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->stringEscapeCharHandler->unescape( $token->patternMatch->text)
						),
						$token->sourcePosition
					);
					$this->s->move(840);
				},
				T::type_keyword->name => $t = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(840);
				},
				T::type_proxy_keyword->name => $t,
				T::arithmetic_op_multiply->name => $t,
				T::sequence_start->name => $t, 
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(841);
				},
				T::type->name => $c,
				T::rest_type->name => 856,
				T::default_match->name => 851,
				T::colon->name => 860,
				T::tuple_start->name => function(LT $token) {
					$this->s->stay(852);
				},
			]],
			840 => ['name' => 'module level tuple or record decider', 'transitions' => [
				T::colon->name => function(LT $token) {
					$this->s->move(842);
				},
				'' => function(LT $token) {
					$this->s->back(852);
				},
			]],
			841 => ['name' => 'module level record colon', 'transitions' => [
				T::colon->name => 842,
			]],
			842 => ['name' => 'module level record value type', 'transitions' => [
				T::optional_key->name => function(LT $token) {
					$this->s->result['current_key'] ??=
                        $this->s->result['first_token']->patternMatch->text;
					$this->s->push(794);
					$this->s->move(4000);
				},
				'' => function(LT $token) {
					$this->s->result['current_key'] ??=
                        $this->s->result['first_token']->patternMatch->text;
					if ($token->rule->tag === T::type_keyword->name && $token->patternMatch->text === 'OptionalKey') {
						$this->s->move(789);
						return;
					}
					$this->s->push(843);
					$this->s->stay(4000);
				},
			]],
			843 => ['name' => 'module level record value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(844);
				}
			]],
			844 => ['name' => 'module level record value separator', 'transitions' => [
				T::tuple_end->name => 845,
				T::value_separator->name => 846,
			]],
			845 => ['name' => 'module level record value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			846 => ['name' => 'module level record key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->stringEscapeCharHandler->unescape( $token->patternMatch->text);
					$this->s->move(841);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(841);
				},
				T::type_keyword->name => $c,
				T::type->name => $c,
				T::mutable->name => $c,
				T::val->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::null->name => $c,
				T::rest_type->name => 847,
				T::default_match->name => 851,
			]],
			847 => ['name' => 'module level record rest', 'transitions' => [
				T::tuple_end->name => 850,
				'' => function(LT $token) {
					$this->s->push(848);
					$this->s->stay(4000);
				},
			]],
			848 => ['name' => 'module level record value rest return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['restType'] = $this->s->generated;
					$this->s->stay(849);
				}
			]],
			849 => ['name' => 'module level record value end', 'transitions' => [
				T::tuple_end->name => 850
			]],
			850 => ['name' => 'module level record value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType(
						$this->s->result['compositeValues'],
						$this->s->result['restType'] ?? $this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			851 => ['name' => 'module level record key is type name', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$typeName = $token->patternMatch->text;
					$recordKey = lcfirst($typeName);
					$this->s->result['compositeValues'][$recordKey] =
						$this->nodeBuilder->namedType(
							new TypeNameIdentifier($typeName)
						);
					$this->s->move(844);
				},
			]],
			852 => ['name' => 'module level tuple value type', 'transitions' => [
				T::rest_type->name => 856,
				'' => function(LT $token) {
					$this->s->push(853);
					$this->s->stay(4000);
				},
			]],
			853 => ['name' => 'module level tuple value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(854);
				}
			]],
			854 => ['name' => 'module level tuple value separator', 'transitions' => [
				T::tuple_end->name => 855,
				T::value_separator->name => 852,
			]],
			855 => ['name' => 'module level tuple value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			856 => ['name' => 'module level tuple rest', 'transitions' => [
				T::tuple_end->name => 859,
				'' => function(LT $token) {
					$this->s->push(857);
					$this->s->stay(4000);
				},
			]],
			857 => ['name' => 'module level tuple value rest return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['restType'] = $this->s->generated;
					$this->s->stay(858);
				}
			]],
			858 => ['name' => 'module level tuple value end', 'transitions' => [
				T::tuple_end->name => 859
			]],
			859 => ['name' => 'module level tuple value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType(
						$this->s->result['compositeValues'],
						$this->s->result['restType'] ?? $this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			860 => ['name' => 'module level tuple value end', 'transitions' => [
				T::rest_type->name => 847
			]],
			870 => ['name' => 'integer interval init', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'] = [];
					$this->s->stay(871);
				}
			]],
			871 => ['name' => 'integer interval start', 'transitions' => [
				T::call_start->name => 872,
				T::tuple_start->name => 873,
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(877);
				},
				T::integer_number->name => $c,
			]],
			872 => ['name' => 'integer interval open start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(874);
				},
				T::integer_number->name => $c,
				T::range_dots->name => function(LT $token) {
					$this->s->result['intervalStart'] = MinusInfinity::value;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(875);
				},
			]],
			873 => ['name' => 'integer interval closed start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->move(874);
				},
				T::integer_number->name => $c,
			]],
			874 => ['name' => 'integer interval dots', 'transitions' => [
				T::range_dots->name => 875
			]],
			875 => ['name' => 'integer interval end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->move(876);
				},
				T::integer_number->name => $c,
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEnd'] = PlusInfinity::value;
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(877);
				},
			]],
			876 => ['name' => 'integer interval bracket', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(877);
				},
				T::tuple_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(877);
				},
			]],
			877 => ['name' => 'integer interval add', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'][] = $this->nodeBuilder->numberInterval(
						$this->s->result['intervalStart'] === MinusInfinity::value ?
							MinusInfinity::value : new NumberIntervalEndpoint(
								new Number($this->s->result['intervalStart']),
								$this->s->result['intervalStartIsInclusive'],
							),
						$this->s->result['intervalEnd'] === PlusInfinity::value ?
							PlusInfinity::value : new NumberIntervalEndpoint(
							new Number($this->s->result['intervalEnd']),
								$this->s->result['intervalEndIsInclusive'],
							),
					);
					$this->s->stay(878);
				}
			]],
			878 => ['name' => 'integer interval separator', 'transitions' => [
				T::value_separator->name => 871,
				'' => function(LT $token) {
					$this->s->generated = $this->s->result['intervals'];
					$this->s->pop();
				}
			]],
			880 => ['name' => 'real interval init', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'] = [];
					$this->s->stay(881);
				}
			]],
			881 => ['name' => 'real interval start', 'transitions' => [
				T::call_start->name => 882,
				T::tuple_start->name => 883,
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(887);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
			]],
			882 => ['name' => 'real interval open start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(884);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::range_dots->name => function(LT $token) {
					$this->s->result['intervalStart'] = MinusInfinity::value;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(885);
				},
			]],
			883 => ['name' => 'real interval closed start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->move(884);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
			]],
			884 => ['name' => 'real interval dots', 'transitions' => [
				T::range_dots->name => 885
			]],
			885 => ['name' => 'real interval end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->move(886);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEnd'] = PlusInfinity::value;
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(887);
				},
			]],
			886 => ['name' => 'real interval bracket', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(887);
				},
				T::tuple_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(887);
				},
			]],
			887 => ['name' => 'real interval add', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'][] = $this->nodeBuilder->numberInterval(
						$this->s->result['intervalStart'] === MinusInfinity::value ?
							MinusInfinity::value : new NumberIntervalEndpoint(
								new Number($this->s->result['intervalStart']),
								$this->s->result['intervalStartIsInclusive'],
							),
						$this->s->result['intervalEnd'] === PlusInfinity::value ?
							PlusInfinity::value : new NumberIntervalEndpoint(
							new Number($this->s->result['intervalEnd']),
								$this->s->result['intervalEndIsInclusive'],
							),
					);
					$this->s->stay(888);
				}
			]],
			888 => ['name' => 'real interval separator', 'transitions' => [
				T::value_separator->name => 881,
				'' => function(LT $token) {
					$this->s->generated = $this->s->result['intervals'];
					$this->s->pop();
				}
			]]
		];		
	}

	private function noErrorMethodCall(bool $useGenerated): void {
		$parameter = $this->s->result['expression_left'];
		$this->s->generated = $this->nodeBuilder->methodCall(
			$parameter,
			new MethodNameIdentifier($this->s->result['method_name']),
			$useGenerated ? $this->s->generated :
				$this->nodeBuilder->constant($this->nodeBuilder->nullValue)
		);
		if ($this->s->result['is_no_external_error'] ?? false) {
			$this->s->generated = $this->nodeBuilder->noExternalError($this->s->generated);
		} elseif ($this->s->result['is_no_error'] ?? false) {
			$this->s->generated = $this->nodeBuilder->noError($this->s->generated);
		}
	}
}
