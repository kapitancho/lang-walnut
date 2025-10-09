<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\AST\Parser;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\UnionTypeNode;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
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
		private ParserState $s,
		private NodeBuilder $nodeBuilder,
		private EscapeCharHandler $escapeCharHandler
	) {}

	public function getAllStates(): array {
		return [
			-1 => ['name' => 'EOF', 'transitions' => [
				'' => function(LT $token) {
					$this->s->move(-1);
				},
			]],
			101 => ['name' => 'module start', 'transitions' => [
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

					$this->s->move(102);
				}
			]],
			102 => ['name' => 'module content start', 'transitions' => [
				T::cli_entry_point->name => function(LT $token) {
					$this->s->result['dependencyType'] = $this->nodeBuilder->nothingType;
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(113);
					$this->s->move(201);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(114);
					$this->s->move(701);
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
					$this->s->move(119);
				},
				'EOF' => -1
			]],
			104 => ['name' => 'module level type definition', 'transitions' => [
				T::named_type->name => 115,
				T::assign->name => 105,
				T::cast_marker->name => 119,
				T::method_marker->name => 122,
				T::call_start->name => function(LT $token) {
					$this->s->push(141);
					$this->s->move(611);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->tupleType([]);
					$this->s->move(144);
				},
				T::empty_record->name => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->recordType([]);
					$this->s->move(144);
				},
				T::tuple_start->name => 142,
			]],
			105 => ['name' => 'module level type assignment', 'transitions' => [
				//T::atom_type->name => 106,
				/*T::enum_type_start->name => function(LT $token) {
					$this->s->result['enumerationValues'] = [];
					$this->s->move(107);
				},*/
				//T::colon->name => 112,
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(131);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::sequence_start->name => $c, //Shape<T>
				T::lambda_param->name => $c,
				T::tuple_start->name => $c,
				T::call_start->name => $c,
				T::empty_tuple->name => 810,
				T::empty_record->name => 811,
			]],

			106 => ['name' => 'module level atom', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAtom(
							new TypeNameIdentifier($this->s->result['typeName'])
						)
					);
					$this->s->move(102);
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
				T::call_end->name => 109
			]],
			109 => ['name' => 'module level enum end', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addEnumeration(
							new TypeNameIdentifier($this->s->result['typeName']),
							array_map(
								static fn(string $value): EnumValueIdentifier => new EnumValueIdentifier($value),
								$this->s->result['enumerationValues'] ?? []
							)
						)
					);
					$this->s->move(102);
				}
			]],

			110 => ['name' => 'sealed type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(164);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c, //Shape<T>
				T::lambda_param->name => $c,
			]],
			111 => ['name' => 'open type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(159);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c, //Shape<T>
				T::lambda_param->name => $c,
			]],
			112 => ['name' => 'data type type', 'transitions' => [
				T::tuple_start->name => $c = function(LT $token) {
					$this->s->push(154);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c,
				T::lambda_param->name => $c,
			]],

			113 => ['name' => 'module level cli entry point return', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
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
					$this->s->move(102);
				},
			]],
			114 => ['name' => 'module level cli entry point dependency type return', 'transitions' => [
				T::cli_entry_point->name => function(LT $token) {
					$this->s->result['dependencyType'] = $this->s->generated;
					$this->s->push(113);
					$this->s->move(201);
				},
			]],
			115 => ['name' => 'module level named type creation', 'transitions' => [
				T::call_start->name => 116,
				T::special_var_param->name => 111,
				T::this_var->name => 110,
				'' => function(LT $token) {
					$this->s->stay(112);
				},
			]],
			116 => ['name' => 'module level atom or enum', 'transitions' => [
				T::call_end->name => 106,
				'' => function(LT $token) {
					$this->s->result['enumerationValues'] = [];
					$this->s->stay(107);
				},
			]],

			154 => ['name' => 'data base type return', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addData(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->generated,
						)
					);
					$this->s->move(102);
				}
			]],

			159 => ['name' => 'open base type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['value_type'] = $this->s->generated;
					$this->s->stay(160);
				}
			]],
			160 => ['name' => 'open error type', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(161);
					$this->s->move(701);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(163);
					$this->s->move(201);
				},
				T::expression_separator->name => function(LT $token) {
					$this->s->generated = null; /*$this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);*/
					$this->s->stay(163);
				},
			]],
			161 => ['name' => 'open error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(162);
				}
			]],
			162 => ['name' => 'open error type body start', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(163);
					$this->s->move(201);
				},
			]],
			163 => ['name' => 'open result', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addOpen(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->result['value_type'],
							$this->s->generated,
							//$this->nodeBuilder->functionBody($this->s->generated),
							$this->s->result['error_type'] ?? null,
						)
					);
					$this->s->move(102);
				}
			]],
			164 => ['name' => 'sealed base type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['value_type'] = $this->s->generated;
					$this->s->stay(165);
				}
			]],
			165 => ['name' => 'sealed error type', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(166);
					$this->s->move(701);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(168);
					$this->s->move(201);
				},
				T::expression_separator->name => function(LT $token) {
					$this->s->generated = null;/*$this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);*/
					$this->s->stay(168);
				},
			]],
			166 => ['name' => 'sealed error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(167);
				}
			]],
			167 => ['name' => 'sealed error type body start', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(168);
					$this->s->move(201);
				},
			]],
			168 => ['name' => 'sealed result', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addSealed(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->result['value_type'],
							$this->s->generated,
							//$this->nodeBuilder->functionBody($this->s->generated),
							$this->s->result['error_type'] ?? null,
						)
					);
					$this->s->move(102);
				}
			]],




			119 => ['name' => 'cast base type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['castToTypeName'] = $token->patternMatch->text;
					$this->s->move(120);
				}
			]],
			120 => ['name' => 'cast body marker', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(129);
					$this->s->move(701);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->move(156);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(121);
					$this->s->move(201);
				},
			]],
			121 => ['name' => 'cast body result', 'transitions' => [
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
					$this->s->move(102);
				}
			]],
			122 => ['name' => 'method definition start', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['method_name'] = $token->patternMatch->text;
					$this->s->move(123);
				},
				T::type_keyword->name => $c
			]],

			123 => ['name' => 'method name next', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->push(124);
					$this->s->move(602);
				}
			]],
			124 => ['name' => 'method name next parameter', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->nodeBuilder->functionType(
						$this->s->generated['parameter_type'],
						$this->s->generated['return_type'],
					);
					$this->s->move(126);
				}
			]],
			126 => ['name' => 'method name body', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(151);
					$this->s->move(651);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(127);
					$this->s->move(201);
				},
			]],
			127 => ['name' => 'method name result', 'transitions' => [
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
					$this->s->move(102);
				}
			]],
			129 => ['name' => 'cast error type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(130);
				}
			]],
			130 => ['name' => 'cast error type return', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->move(156);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(121);
					$this->s->move(201);
				},
			]],

			131 => ['name' => 'module level type alias end', 'transitions' => [
				'' => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->s->generated
						)
					);
					$this->s->stay(132);
				},
			]],
			132 => ['name' => 'module level separator', 'transitions' => [
				T::expression_separator->name => 102
			]],


			134 => ['name' => 'variable name separator', 'transitions' => [
				T::expression_separator->name => 102
			]],

			141 => ['name' => 'constructor method parameter', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->s->generated['parameter_type'];
					$this->s->move(144);
				},
			]],

			142 => ['name' => 'constructor method parameter tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(146);
					$this->s->back(701);
				}
			]],
			144 => ['name' => 'constructor method body', 'transitions' => [
				T::error_marker->name => function(LT $token) {
					$this->s->push(147);
					$this->s->move(701);
				},
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(152);
					$this->s->move(651);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(145);
					$this->s->move(201);
				},
			]],
			145 => ['name' => 'constructor method result', 'transitions' => [
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
					$this->s->move(102);
				}
			]],
			146 => ['name' => 'constructor method tuple or record parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->s->generated;
					$this->s->stay(144);
				}
			]],
			147 => ['name' => 'constructor method error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(148);
				}
			]],
			148 => ['name' => 'constructor method body after error type', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(152);
					$this->s->move(651);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->push(145);
					$this->s->move(201);
				},
			]],
			151 => ['name' => 'method dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(127);
					$this->s->move(201);
				},
			]],
			152 => ['name' => 'constructor dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(145);
					$this->s->move(201);
				},
			]],
			153 => ['name' => 'cast dependency result', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->push(121);
					$this->s->move(201);
				}
			]],
			155 => ['name' => 'cast dependency result shortcut', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated;
					$this->s->push(121);
					$this->s->move(201);
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
					$this->s->stay(121);
				},
			]],
			156 => ['name' => 'cast dependency type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->push(155);
					$this->s->stay(701);
				},
				'' => function(LT $token) {
					$this->s->push(153);
					$this->s->stay(651);
				},
			]],

			201 => ['name' => 'expression adt start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(302);
					$this->s->stay(301);
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
					//$this->s->moveAndPop();
					$this->s->pop();
				}
			]],

			230 => ['name' => 'expression sequence', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(231);
					$this->s->stay(201);
				}
			]],
			231 => ['name' => 'expression sequence separator', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->s->result['sequence_expressions'][] = $this->s->generated;
					$this->s->push(231);
					$this->s->move(201);
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

			240 => ['name' => 'expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->push(241);
					$this->s->stay(201);
				}
			]],
			241 => ['name' => 'expression sequence return', 'transitions' => [
				'' => function(LT $token) {
					$result = $this->s->generated;
					$this->s->generated = $this->nodeBuilder->return($result);
					$this->s->pop();
				},
			]],

			250 => ['name' => 'expression no error', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->push(251);
					$this->s->move(201);
				}
			]],
			251 => ['name' => 'expression no error return', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$result = $this->s->generated;
					$this->s->i++;
					$this->s->generated = $this->nodeBuilder->noError($result);
					$this->s->pop();
				},
			]],
			252 => ['name' => 'expression no external error', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->result['sequence_expressions'] = [];
					$this->s->push(253);
					$this->s->move(201);
				}
			]],
			253 => ['name' => 'expression no external error return', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$result = $this->s->generated;
					$this->s->i++;
					$this->s->generated = $this->nodeBuilder->noExternalError($result);
					$this->s->pop();
				},
			]],
			260 => ['name' => 'var expression', 'transitions' => [
				T::assign->name => 261,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->variableName(
						new VariableNameIdentifier($this->s->result['var_name'])
					);
					$this->s->pop();
				}
			]],
			261 => ['name' => 'assign expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(262);
					$this->s->stay(201);
				}
			]],
			262 => ['name' => 'assign expression value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->variableAssignment(
						new VariableNameIdentifier($this->s->result['var_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],


			269 => ['name' => 'value data expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->data(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				},
			]],


			270 => ['name' => 'type expression', 'transitions' => [
				T::boolean_op_not->name => function (LT $token) {
					$this->s->push(269);
					$this->s->move(301);
				},
				T::property_accessor->name => function(LT $token) {
					$this->s->push(274);
					$this->s->back(401);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->tupleValue([])
					);
					$this->s->move(275);
				},
				T::empty_set->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->setValue([])
					);
					$this->s->move(275);
				},
				T::empty_record->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->recordValue([])
					);
					$this->s->move(275);
				},
				T::call_start->name => 271,
				T::tuple_start->name => function(LT $token) {
					$this->s->push(273);
					$this->s->stay(201);
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
			271 => ['name' => 'constructor call expression', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->nodeBuilder->nullValue
					);
					$this->s->stay(272);
				},
				'' => function(LT $token) {
					$this->s->push(272);
					$this->s->stay(201);
				}
			]],
			272 => ['name' => 'constructor call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->moveAndPop();
				}
			]],
			273 => ['name' => 'constructor call value tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			274 => ['name' => 'enum value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant($this->s->generated);
					$this->s->pop();
				}
			]],
			275 => ['name' => 'constructor call empty value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier($this->s->result['type_name']),
						$this->s->generated
					);
					$this->s->pop();
				}
			]],

			280 => ['name' => 'list or dict expression', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->escapeCharHandler->unescape(
								$token->patternMatch->text
							)
						),
						$token->sourcePosition
					);
					$this->s->move(281);
				},
				T::word->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(281);
				},
				T::var_keyword->name => $c,
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				'' => function(LT $token) {
					$this->s->stay(287);
				},
			]],
			281 => ['name' => 'list or dict expression separator', 'transitions' => [
				T::colon->name => 282,
				'' => function(LT $token) {
					$this->s->back(287);
				}
			]],
			282 => ['name' => 'dict expression expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['current_key'] ??= $this->s->result['first_token']->patternMatch->text;
					$this->s->push(283);
					$this->s->stay(201);
				},
			]],
			283 => ['name' => 'dict expression dict expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(284);
				}
			]],
			284 => ['name' => 'dict expression dict expression separator', 'transitions' => [
				T::tuple_end->name => 285,
				T::value_separator->name => 286,
			]],
			285 => ['name' => 'dict expression dict expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->record(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			286 => ['name' => 'dict expression dict expression key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->escapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(299);
				},
				T::word->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(299);
				},
				T::var_keyword->name => $c,
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				T::mutable->name => $c,
				T::val->name => $c,
			]],
			287 => ['name' => 'list or set expression expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(288);
					$this->s->stay(201);
				},
			]],
			288 => ['name' => 'list or set expression list expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(289);
				}
			]],
			289 => ['name' => 'list or set expression list expression separator', 'transitions' => [
				T::tuple_end->name => 294,
				T::value_separator->name => 291,
				T::expression_separator->name => 290,
			]],
			290 => ['name' => 'list expression set expression', 'transitions' => [
				T::tuple_end->name => 298,
				'' => function(LT $token) {
					$this->s->stay(295);
				},
			]],
			291 => ['name' => 'list expression tuple expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(292);
					$this->s->stay(201);
				},
			]],
			292 => ['name' => 'list expression list expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(293);
				}
			]],
			293 => ['name' => 'list expression list expression separator', 'transitions' => [
				T::tuple_end->name => 294,
				T::value_separator->name => 291,
			]],
			294 => ['name' => 'list expression list expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tuple(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			295 => ['name' => 'list expression set expression', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(296);
					$this->s->stay(201);
				},
			]],
			296 => ['name' => 'list expression set expression return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(297);
				}
			]],
			297 => ['name' => 'list expression set expression separator', 'transitions' => [
				T::tuple_end->name => 298,
				T::expression_separator->name => 295,
			]],
			298 => ['name' => 'list expression set expression return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->set(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			299 => ['name' => 'dict expression separator', 'transitions' => [
				T::colon->name => 282
			]],


			301 => ['name' => 'expression start', 'transitions' => [
				T::string_value->name => $c = function(LT $token) {
					$this->s->stay(202);
				},
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

				T::error_marker->name => function(LT $token) { $this->s->move(354); },
				T::mutable->name => function(LT $token) { $this->s->move(356); },
				T::val->name => function(LT $token) { $this->s->move(381); },

				T::sequence_start->name => -230,
				T::sequence_end->name => function(LT $token) { $this->s->stay(318); },
				T::lambda_return->name => -240,
				T::no_error->name => -250,
				T::no_external_error->name => -252,

				T::function_body_marker->name => -351,

				T::boolean_op->name => $u = function(LT $token) { $this->s->stay(361); },
				T::boolean_op_not->name => $u,
				T::arithmetic_op->name => $u,
				T::default_match->name => $u,

				T::var->name => -631,

				T::var_keyword->name => function(LT $token) {
					$this->s->result = ['var_name' => $token->patternMatch->text];
					$this->s->move(260);
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
					$this->s->move(270);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(280);
				},
				T::when_value_of->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchValue';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(320);
				},
				T::when_type_of->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchType';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(320);
				},
				T::when_is_true->name => function(LT $token) {
					$this->s->result['matchType'] = 'isTrue';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(324);
				},
				T::when_is_error->name => function(LT $token) {
					$this->s->result['matchType'] = 'isError';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(365);
				},
				T::when->name => function(LT $token) {
					$this->s->result['matchType'] = 'matchIf';
					$this->s->result['matchPairs'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(335);
				},
			]],

			302 => ['name' => 'property method or call', 'transitions' => [
				T::property_accessor->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(303);
				},
				T::pure_marker->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = true;
					$this->s->result['is_no_error'] = false;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(305);
				},
				T::method_marker->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = false;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(305);
				},
				T::lambda_return->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = true;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(305);
				},
				T::error_as_external->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['is_no_external_error'] = false;
					$this->s->result['is_no_error'] = true;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['method_name'] = 'errorAsExternal';
					$this->s->move(306);
				},
				T::call_start->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->move(311);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->stay(313);
				},
				T::empty_tuple->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->generated,
						$this->nodeBuilder->constant($this->nodeBuilder->tupleValue([]))
					);
					$this->s->move(315);
				},
				T::empty_record->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->generated,
						$this->nodeBuilder->constant($this->nodeBuilder->recordValue([]))
					);
					$this->s->move(315);
				},
				T::empty_set->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->generated,
						$this->nodeBuilder->constant($this->nodeBuilder->setValue([]))
					);
					$this->s->move(315);
				},
				T::arithmetic_op->name => $c = function(LT $token) {
					/*if ($token->patternMatch->text === '$') {
						$this->s->pop();
						return;
					}*/
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['expression_left'] = $this->s->generated;
					$this->s->result['method_name'] = match($token->patternMatch->text) {
						'+' => 'binaryPlus',
						'-' => 'binaryMinus',
						'*' => 'binaryMultiply',
						'/' => 'binaryDivide',
						'//' => 'binaryIntegerDivide',
						'%' => 'binaryModulo',
						'**' => 'binaryPower',
						'&' => 'binaryBitwiseAnd',
						'|' => 'binaryBitwiseOr',
						'^' => 'binaryBitwiseXor',
						'<' => 'binaryLessThan',
						'<=' => 'binaryLessThanEqual',
						'>' => 'binaryGreaterThan',
						'>=' => 'binaryGreaterThanEqual',
						'!=' => 'binaryNotEqual',
						'==' => 'binaryEqual',
						'||' => 'binaryOr',
						'&&' => 'binaryAnd',
						'^^' => 'binaryXor',
					};
					$this->s->move(316);
				},

				//binary operators start
				T::boolean_op->name => $c,
				//T::boolean_op_not->name => $c,
				T::less_than_equal->name => $c,
				T::greater_than_equal->name => $c,
				T::intersection->name => $c,
				T::union->name => $c,
				T::arithmetic_op2->name => $c,
				T::arithmetic_op_multiply->name => $c,
				T::equals->name => $c,
				T::not_equals->name => $c,
				T::type_start->name => $c,
				T::type_end->name => $c,
				T::lambda_param->name => $c,
				//binary operators end


				//T::this_var->name => $c,
				T::special_var_modulo->name => $c,
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			303 => ['name' => 'property name', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->propertyAccess(
						$this->s->result['expression_left'],
						$this->escapeCharHandler->unescape( $token->patternMatch->text)
					);
					$this->s->move(304);
				},
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->propertyAccess(
						$this->s->result['expression_left'],
						is_numeric($token->patternMatch->text) ?
							(int)$token->patternMatch->text :
							$token->patternMatch->text
					);
					$this->s->move(304);
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
			304 => ['name' => 'property name next', 'transitions' => [
				T::property_accessor->name => $c = function(LT $token) {
					$this->s->stay(302);
				},
				//binary operators start
				T::boolean_op->name => $c,
				T::boolean_op_not->name => $c,
				T::less_than_equal->name => $c,
				T::greater_than_equal->name => $c,
				T::arithmetic_op->name => $c,
				T::intersection->name => $c,
				T::union->name => $c,
				T::arithmetic_op2->name => $c,
				T::arithmetic_op_multiply->name => $c,
				T::equals->name => $c,
				T::not_equals->name => $c,
				T::type_start->name => $c,
				T::type_end->name => $c,
				T::lambda_param->name => $c,
				//binary operators end

				T::this_var->name => $c,
				T::special_var->name => $c,
				T::special_var_param->name => $c,
				T::special_var_modulo->name => $c,
				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::error_as_external->name => $c,
				T::lambda_return->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,
				T::tuple_end->name => $c,
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			305 => ['name' => 'method name', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['method_name'] = $token->patternMatch->text;
					$this->s->move(306);
				},
				T::type_keyword->name => $c,
				T::type->name => $c
			]],
			306 => ['name' => 'method name next', 'transitions' => [
				T::call_start->name => function(LT $token) {
					$this->s->move(307);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->stay(309);
				},
				T::property_accessor->name => $c = function(LT $token) {
					$this->noErrorMethodCall(false);
					$this->s->stay(302);
				},
				//binary operators start
				T::boolean_op->name => $c,
				T::boolean_op_not->name => $c,
				T::less_than_equal->name => $c,
				T::greater_than_equal->name => $c,
				T::arithmetic_op->name => $c,
				T::intersection->name => $c,
				T::union->name => $c,
				T::arithmetic_op2->name => $c,
				T::arithmetic_op_multiply->name => $c,
				T::equals->name => $c,
				T::not_equals->name => $c,
				T::type_start->name => $c,
				T::type_end->name => $c,
				T::lambda_param->name => $c,
				//binary operators end

				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::error_as_external->name => $c,
				T::lambda_return->name => $c,
				'' => function(LT $token) {
					$this->noErrorMethodCall(false);
					$this->s->pop();
				},
			]],
			307 => ['name' => 'method call start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(308);
					$this->s->stay(201);
				}
			]],
			308 => ['name' => 'method call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->noErrorMethodCall(true);
					$this->s->move(315);
				}
			]],
			309 => ['name' => 'method call start tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(310);
					$this->s->stay(201);
				}
			]],
			310 => ['name' => 'method call value tuple or record', 'transitions' => [
				/*T::property_accessor->name => $c = function(LT $token) {
					$this->noErrorMethodCall(true);
					$this->s->stay(302);
				},
				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::lambda_return->name => $c,
				T::error_as_external->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,*/
				'' => function(LT $token) {
					$this->noErrorMethodCall(true);
					$this->s->pop();
				}
			]],
			311 => ['name' => 'function call start', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->nodeBuilder->constant($this->nodeBuilder->nullValue)
					);
					$this->s->move(315);
				},
				'' => function(LT $token) {
					$this->s->push(312);
					$this->s->stay(201);
				}
			]],
			312 => ['name' => 'function call value', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->move(315);
				}
			]],
			313 => ['name' => 'function call start tuple or record', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(314);
					$this->s->stay(201);
				}
			]],
			314 => ['name' => 'function call value tuple or record', 'transitions' => [
				/*T::property_accessor->name => $c = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->stay(302);
				},
				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::lambda_return->name => $c,
				T::error_as_external->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,*/
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->functionCall(
						$this->s->result['expression_left'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],
			315 => ['name' => 'method call value', 'transitions' => [
				T::property_accessor->name => $c = function(LT $token) {
					$this->s->stay(302);
				},
				//binary operators start
				T::boolean_op->name => $c,
				T::boolean_op_not->name => $c,
				T::less_than_equal->name => $c,
				T::greater_than_equal->name => $c,
				T::arithmetic_op->name => $c,
				T::intersection->name => $c,
				T::union->name => $c,
				T::arithmetic_op2->name => $c,
				T::arithmetic_op_multiply->name => $c,
				T::equals->name => $c,
				T::not_equals->name => $c,
				T::type_start->name => $c,
				T::type_end->name => $c,
				T::lambda_param->name => $c,
				//binary operators end

				T::pure_marker->name => $c,
				T::method_marker->name => $c,
				T::lambda_return->name => $c,
				T::error_as_external->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,
				'' => function(LT $token) {
					$this->s->pop();
				}
			]],

			316 => ['name' => 'method call arithmetic start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(317);
					$this->s->stay(201);
				}
			]],
			317 => ['name' => 'method call arithmetic value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->methodCall(
						$this->s->result['expression_left'],
						new MethodNameIdentifier($this->s->result['method_name']),
						$this->s->generated
					);
					$this->s->stay(315);
				}
			]],
			318 => ['name' => 'sequence early end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->sequence([]);
					$this->s->pop();
				}
			]],

			320 => ['name' => 'match value of start', 'transitions' => [
				T::call_start->name => 321
			]],
			321 => ['name' => 'match value of target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(322);
					$this->s->stay(201);
				}
			]],
			322 => ['name' => 'match value of target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(323);
				}
			]],
			323 => ['name' => 'match value is', 'transitions' => [
				T::when_value_is->name => 324
			]],
			324 => ['name' => 'match value of target end', 'transitions' => [
				T::sequence_start->name => 325
			]],
			325 => ['name' => 'match value pair start', 'transitions' => [
				T::default_match->name => 330,
				'' => function(LT $token) {
					$this->s->push(326);
					$this->s->stay(201);
				}
			]],
			326 => ['name' => 'match value pair match return', 'transitions' => [
				T::colon->name => function(LT $token) {
					$this->s->result['matchPairMatch'] = $this->s->generated;
					$this->s->move(327);
				}
			]],
			327 => ['name' => 'match value pair value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(328);
					$this->s->stay(201);
				}
			]],
			328 => ['name' => 'match value pair value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['matchPairs'][] = $this->nodeBuilder->matchPair(
						$this->s->result['matchPairMatch'],
						$this->s->generated
					);
					$this->s->stay(329);
				}
			]],
			329 => ['name' => 'match value pair separator', 'transitions' => [
				T::value_separator->name => 325,
				T::sequence_end->name => 333
			]],
			330 => ['name' => 'match value pair match return', 'transitions' => [
				T::colon->name => 331
			]],
			331 => ['name' => 'match value default pair start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(332);
					$this->s->stay(201);
				}
			]],
			332 => ['name' => 'match value pair value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['matchPairs'][] = $this->nodeBuilder->matchDefault(
						$this->s->generated
					);
					$this->s->stay(329);
				}
			]],
			333 => ['name' => 'match value pair match return', 'transitions' => [
				'' => function(LT $token) {
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
			335 => ['name' => 'match if start', 'transitions' => [
				T::call_start->name => 336
			]],
			336 => ['name' => 'match if target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(337);
					$this->s->stay(201);
				}
			]],
			337 => ['name' => 'match if target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(338);
				}
			]],
			338 => ['name' => 'match if then start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(339);
					$this->s->stay(201);
				}
			]],
			339 => ['name' => 'match if else check', 'transitions' => [
				T::default_match->name => function(LT $token) {
					$this->s->result['matchThen'] = $this->s->generated;
					$this->s->move(340);
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
			340 => ['name' => 'match if else start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(341);
					$this->s->stay(201);
				}
			]],
			341 => ['name' => 'match if else check', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchIf(
						$this->s->result['matchTarget'],
						$this->s->result['matchThen'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],

			351 => ['name' => 'scoped expression start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(352);
					$this->s->stay(201);
				},
			]],

			352 => ['name' => 'scoped expression end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->scoped($this->s->generated);
					$this->s->pop();
				},
			]],

			354 => ['name' => 'error value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(355);
					$this->s->stay(301);
				},
			]],
			355 => ['name' => 'error value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constructorCall(
						new TypeNameIdentifier('Error'),
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			356 => ['name' => 'mutable value', 'transitions' => [
				T::sequence_start->name => 357,
			]],
			357 => ['name' => 'mutable value type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(358);
					$this->s->stay(701);
				},
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			358 => ['name' => 'mutable value type separator', 'transitions' => [
				T::value_separator->name => 359,
			]],
			359 => ['name' => 'mutable value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['mutable_type'] = $this->s->generated;
					$this->s->push(360);
					$this->s->stay(301);
				},
			]],
			360 => ['name' => 'mutable value type return', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutable(
						$this->s->result['mutable_type'],
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			361 => ['name' => 'unary op start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['method_name'] = match($token->patternMatch->text) {
						'+' => 'unaryPlus',
						'-' => 'unaryMinus',
						'~' => 'unaryBitwiseNot',
						'!' => 'unaryNot',
						// @codeCoverageIgnoreStart
						default => 'unaryUnknown',
						// @codeCoverageIgnoreEnd
					};
					$this->s->push(362);
					$this->s->move(301);
				},
			]],
			362 => ['name' => 'unary op return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->stay(363);
				}
			]],
			363 => ['name' => 'unary op return end', 'transitions' => [
				'' => function(LT $token) {
					$g = $this->s->generated;
					$m = $this->s->result['method_name'];
					$this->s->pop();
					$this->s->generated = $this->nodeBuilder->methodCall(
						$g,
						new MethodNameIdentifier($m),
						$this->nodeBuilder->constant(
							$this->nodeBuilder->nullValue
						)
					);
				},
			]],

			365 => ['name' => 'match error start', 'transitions' => [
				T::call_start->name => 366
			]],
			366 => ['name' => 'match error target', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(367);
					$this->s->stay(201);
				}
			]],
			367 => ['name' => 'match error target end', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['matchTarget'] = $this->s->generated;
					$this->s->move(368);
				}
			]],
			368 => ['name' => 'match error then start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(369);
					$this->s->stay(201);
				}
			]],
			369 => ['name' => 'match error else check', 'transitions' => [
				T::default_match->name => function(LT $token) {
					$this->s->result['matchThen'] = $this->s->generated;
					$this->s->move(370);
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
			370 => ['name' => 'match error start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(371);
					$this->s->stay(201);
				}
			]],
			371 => ['name' => 'match error else check', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->matchError(
						$this->s->result['matchTarget'],
						$this->s->result['matchThen'],
						$this->s->generated
					);
					$this->s->pop();
				}
			]],

			381 => ['name' => 'constant value start', 'transitions' => [
				T::sequence_start->name => function(LT $token) {
					$this->s->push(382);
					$this->s->move(401);
				},
				T::tuple_start->name => function(LT $token) {
					$this->s->push(383);
					$this->s->stay(401);
				},
			]],
			382 => ['name' => 'constant value end', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],
			383 => ['name' => 'constant value tuple end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->constant(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],

			401 => ['name' => 'value start', 'transitions' => [
				T::string_value->name => function(LT $token) { $this->s->stay(420); },
				T::positive_integer_number->name => function(LT $token) { $this->s->stay(430); },
				T::integer_number->name => function(LT $token) { $this->s->stay(430); },
				T::real_number->name => function(LT $token) { $this->s->stay(440); },
				T::empty_tuple->name => function(LT $token) { $this->s->stay(402); },
				T::empty_record->name => function(LT $token) { $this->s->stay(403); },
				T::empty_set->name => function(LT $token) { $this->s->stay(404); },
				T::null->name => function(LT $token) { $this->s->stay(405); },
				T::true->name => function(LT $token) { $this->s->stay(406); },
				T::false->name => function(LT $token) { $this->s->stay(407); },
				T::lambda_param->name => function(LT $token) { $this->s->stay(450); },
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(460);
				},
				T::type->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(480);
				},
				T::type_short->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(479);
				},
				T::error_marker->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(421);
					},
				T::mutable->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(423);
				},
				T::type_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['current_type_name'] = $token->patternMatch->text;
					$this->s->move(488);
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
			420 => ['name' => 'string value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->stringValue(
						$this->escapeCharHandler->unescape( $token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
			]],

			421 => ['name' => 'error constant value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(422);
					$this->s->stay(401);
				},
			]],
			422 => ['name' => 'error constant value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->errorValue(
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			423 => ['name' => 'mutable constant value', 'transitions' => [
				T::sequence_start->name => 424,
			]],
			424 => ['name' => 'mutable constant value type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(425);
					$this->s->stay(701);
				},
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			425 => ['name' => 'mutable constant value type separator', 'transitions' => [
				T::value_separator->name => 426,
			]],
			426 => ['name' => 'mutable constant value value start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['mutable_type'] = $this->s->generated;
					$this->s->push(427);
					$this->s->stay(401);
				},
			]],
			427 => ['name' => 'mutable constant value type return', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableValue(
						$this->s->result['mutable_type'],
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],


			430 => ['name' => 'integer value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerValue(new Number($token->patternMatch->text));
					$this->s->moveAndPop();
				},
			]],
			440 => ['name' => 'real value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realValue(new Number($token->patternMatch->text));
					$this->s->moveAndPop();
				},
			]],
			450 => ['name' => 'function value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(451);
					$this->s->stay(501);
				},
			]],
			451 => ['name' => 'function value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			460 => ['name' => 'list or dict value', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->escapeCharHandler->unescape( $token->patternMatch->text)
						),
						$token->sourcePosition
					);
					$this->s->move(461);
				},
				T::word->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(461);
				},
				T::var_keyword->name => $c,
				T::type_keyword->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
				'' => function(LT $token) {
					$this->s->stay(467);
				},
			]],
			461 => ['name' => 'dict value separator', 'transitions' => [
				T::colon->name => 462,
				'' => function(LT $token) {
					$this->s->back(467);
				}
			]],
			462 => ['name' => 'dict value value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['current_key'] ??= $this->s->result['first_token']->patternMatch->text;
					$this->s->push(463);
					$this->s->stay(401);
				},
			]],
			463 => ['name' => 'dict value dict value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(464);
				}
			]],
			464 => ['name' => 'dict value dict value separator', 'transitions' => [
				T::tuple_end->name => 465,
				T::value_separator->name => 466,
			]],
			465 => ['name' => 'dict value dict value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			466 => ['name' => 'dict value dict value key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->escapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(461);
				},
				T::word->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(461);
				},
				T::var_keyword->name => $c,
				T::val->name => $c,
				T::type_keyword->name => $c,
				T::mutable->name => $c,
				T::null->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::type->name => $c,
			]],
			467 => ['name' => 'list or set value value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(468);
					$this->s->stay(401);
				},
			]],
			468 => ['name' => 'list or set value list value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(469);
				}
			]],
			469 => ['name' => 'list or set value list value separator', 'transitions' => [
				T::tuple_end->name => 474,
				T::value_separator->name => 471,
				T::expression_separator->name => 470,
			]],
			470 => ['name' => 'list value set value', 'transitions' => [
				T::tuple_end->name => 478,
				'' => function(LT $token) {
					$this->s->stay(475);
				},
			]],
			471 => ['name' => 'list value tuple value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(472);
					$this->s->stay(401);
				},
			]],
			472 => ['name' => 'list value list value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(473);
				}
			]],
			473 => ['name' => 'list value list value separator', 'transitions' => [
				T::tuple_end->name => 474,
				T::value_separator->name => 471,
			]],
			474 => ['name' => 'list value list value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			475 => ['name' => 'list value set value', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(476);
					$this->s->stay(401);
				},
			]],
			476 => ['name' => 'list value set value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(477);
				}
			]],
			477 => ['name' => 'list value set value separator', 'transitions' => [
				T::tuple_end->name => 478,
				T::expression_separator->name => 475,
			]],
			478 => ['name' => 'list value set value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setValue(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],

			479 => ['name' => 'type value short', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'EnumerationValue', 'IntegerSubset', 'MutableValue',
						'RealSubset', 'StringSubset', 'State', 'Subset', 'Alias', 'Named'
					], true)) {
						$this->s->generated = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(483);
						return;
					}
					$this->s->push(483);
					$this->s->stay(701);
				},
				T::sequence_start->name => $c, //Shape<T>
				T::arithmetic_op_multiply->name => $c,
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			480 => ['name' => 'type value', 'transitions' => [
				T::tuple_start->name => function(LT $token) {
					$this->s->push(483);
					$this->s->stay(701);
				},
				T::sequence_start->name => 481,
			]],
			481 => ['name' => 'type value type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'EnumerationValue', 'IntegerSubset', 'MutableValue',
						'RealSubset', 'StringSubset', 'State', 'Subset', 'Alias', 'Named'
					], true)) {
						$this->s->generated = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(482);
						return;
					}
					$this->s->push(482);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::tuple_start->name => $c,
				T::lambda_param->name => $c,
			]],
			482 => ['name' => 'type value type separator', 'transitions' => [
				T::sequence_end->name => 483,
			]],
			483 => ['name' => 'type value type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeValue($this->s->generated);
					$this->s->pop();
				},
			]],
			488 => ['name' => 'value type name', 'transitions' => [
				T::property_accessor->name => 492,
				T::boolean_op_not->name => function (LT $token) {
					$this->s->push(493);
					$this->s->move(401);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->atomValue(
						new TypeNameIdentifier($this->s->result['current_type_name']),
					);
					$this->s->pop();
				},
			]],
			492 => ['name' => 'value enum', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->generated = $this->nodeBuilder->enumerationValue(
						new TypeNameIdentifier($this->s->result['current_type_name']),
						new EnumValueIdentifier($token->patternMatch->text)
					);
					$this->s->moveAndPop();
				},
				T::var_keyword->name => $c,
			]],
			493 => ['name' => 'value data return', 'transitions' => [
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
				//T::lambda_param->name => -502
			]],


			502 => ['name' => 'function value parameter return 1', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter'] = $this->s->generated['parameter_type'];
					$this->s->result['return'] = $this->s->generated['return_type'];
					$this->s->stay(503);
				},
			]],

			503 => ['name' => 'function value parameter return 1', 'transitions' => [
				T::dependency_marker->name => function(LT $token) {
					$this->s->push(508);
					$this->s->move(651);
				},
				T::function_body_marker->name => function(LT $token) {
					$this->s->move(506);
				}
			]],

			506 => ['name' => 'function value body return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(507);
					$this->s->stay(201);
				},
			]],
			507 => ['name' => 'function value body return', 'transitions' => [
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
			508 => ['name' => 'function value dependency type', 'transitions' => [
				T::function_body_marker->name => function(LT $token) {
					$this->s->result['dependency'] = $this->s->generated['dependency_type'];
					$this->s->result['dependency_name'] = $this->s->generated['dependency_name'];
					$this->s->move(506);
				}
			]],

			601 => ['name' => 'function value type', 'transitions' => [
				T::lambda_param->name => function(LT $token) {
					$this->s->push(603);
					$this->s->move(611);
				},
			]],
			602 => ['name' => 'method type', 'transitions' => [
				T::lambda_param->name => function(LT $token) {
					$this->s->push(603);
					$this->s->move(611);
				},
				T::lambda_return->name => $c = function(LT $token) {
					$this->s->result['parameter_name'] = null;
					$this->s->result['parameter_type'] = $this->nodeBuilder->nullType;
					$this->s->push(604);
					$this->s->stay(621);
				},
				T::call_end->name => $c,
			]],
			603 => ['name' => 'function or method value type parameter', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_name'] = $this->s->generated['parameter_name'];
					$this->s->result['parameter_type'] = $this->s->generated['parameter_type'];
					$this->s->push(604);
					$this->s->stay(621);
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

			611 => ['name' => 'function value parameter type', 'transitions' => [
				T::var_keyword->name => $d = function(LT $token) {
					$this->s->result['parameter_name'] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(612);
				},
				T::default_match->name => function(LT $token) {
					$this->s->move(614);
				},
				T::arithmetic_op_multiply->name => $c = function(LT $token) {
					$this->s->result['parameter_name'] = null;
					$this->s->push(615);
					$this->s->stay(701);
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
					$this->s->stay(616);
				},
			]],

			612 => ['name' => 'function value parameter name', 'transitions' => [
				T::colon->name => 613,
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->nodeBuilder->anyType;
					$this->s->stay(616);
				}
			]],
			613 => ['name' => 'function value parameter type', 'transitions' => [
				T::arithmetic_op_multiply->name => $c = function(LT $token) {
					$this->s->push(615);
					$this->s->stay(701);
				},
				T::type_proxy_keyword->name => $c,
				T::type_keyword->name => $c,
				T::sequence_start->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
				T::call_start->name => $c,
				T::tuple_start->name => $c,
			]],
			614 => ['name' => 'function value parameter name from type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['parameter_name'] = new VariableNameIdentifier(
						lcfirst($token->patternMatch->text)
					);
					$this->s->push(615);
					$this->s->stay(701);
				},
			]],
			615 => ['name' => 'function value parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['parameter_type'] = $this->s->generated;
					$this->s->stay(616);
				},
			]],
			616 => ['name' => 'function value parameter exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = [
						'parameter_name' => $this->s->result['parameter_name'] ?? null,
						'parameter_type' => $this->s->result['parameter_type']
					];
					$this->s->pop();
				},
			]],

			621 => ['name' => 'function or method value return', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->push(622);
					$this->s->move(701);
				},
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->anyType;
					$this->s->pop();
				},
			]],

			622 => ['name' => 'function or method value return exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],

			631 => ['name' => 'var start', 'transitions' => [
				T::sequence_start->name => 632
			]],
			632 => ['name' => 'var list or dict', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['first_variable_name'] = $token->patternMatch->text;
					$this->s->move(633);
				},
				T::default_match->name => function(LT $token) {
					$this->s->result['variables'] = [];
					$this->s->move(641);
				},
				T::string_value->name => function(LT $token) {
					$this->s->result['next_variable_key'] = $this->escapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(644);
				},
				T::null->name => $c = function(LT $token) {
					$this->s->result['next_variable_key'] = $token->patternMatch->text;
					$this->s->move(644);
				},
				T::true->name => $c,
				T::false->name => $c,
				T::when_value_is->name => $c,
				T::var->name => $c,
				T::val->name => $c,
				T::mutable->name => $c,
				T::type->name => $c,
			]],
			633 => ['name' => 'var list separator', 'transitions' => [
				T::value_separator->name => function(LT $token) {
					$this->s->result['variables'] = [new VariableNameIdentifier($this->s->result['first_variable_name'])];
					$this->s->move(638);
				},
				T::colon->name => function(LT $token) {
					$this->s->result['next_variable_key'] = $this->s->result['first_variable_name'];
					$this->s->move(645);
				},
				T::sequence_end->name => function(LT $token) {
					$this->s->result['variables'] = [new VariableNameIdentifier($this->s->result['first_variable_name'])];
					$this->s->move(639);
				},
			]],
			637 => ['name' => 'var list separator', 'transitions' => [
				T::value_separator->name => 638,
				T::sequence_end->name => 639,
			]],
			638 => ['name' => 'var list next', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(637);
				},
			]],
			639 => ['name' => 'var list assign', 'transitions' => [
				T::assign->name => function(LT $token) {
					$this->s->push(640);
					$this->s->move(201);
				},
			]],
			640 => ['name' => 'var list end', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->multiVariableAssignment(
						$this->s->result['variables'],
						$this->s->generated
					);
					$this->s->pop();
				},
			]],
			641 => ['name' => 'var list assign', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][$token->patternMatch->text] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(642);
				},
			]],
			642 => ['name' => 'var dict separator', 'transitions' => [
				T::value_separator->name => 643,
				T::sequence_end->name => 639,
			]],
			643 => ['name' => 'var dict next', 'transitions' => [
				T::var_keyword->name => $c = function(LT $token) {
					$this->s->result['next_variable_key'] = $token->patternMatch->text;
					$this->s->move(644);
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
					$this->s->result['next_variable_key'] = $this->escapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(644);
				},
				T::default_match->name => 641,
			]],
			644 => ['name' => 'var dict colon', 'transitions' => [
				T::colon->name => 645,
			]],
			645 => ['name' => 'var dict key value', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['variables'][$this->s->result['next_variable_key']] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(642);
				},
			]],


			651 => ['name' => 'dependency parameter start', 'transitions' => [
				T::var_keyword->name => function(LT $token) {
					$this->s->result['dependency_name'] = new VariableNameIdentifier($token->patternMatch->text);
					$this->s->move(652);
				},
				T::default_match->name => function(LT $token) {
					$this->s->move(654);
				},
				'' => function(LT $token) {
					$this->s->result['dependency_name'] = null;
					$this->s->push(655);
					$this->s->stay(701);
				},
			]],

			652 => ['name' => 'dependency parameter name', 'transitions' => [
				T::colon->name => 653,
			]],
			653 => ['name' => 'dependency parameter name type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(655);
					$this->s->stay(701);
				},
			]],
			654 => ['name' => 'dependency parameter name from type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$this->s->result['dependency_name'] = new VariableNameIdentifier(
						lcfirst($token->patternMatch->text)
					);
					$this->s->push(655);
					$this->s->stay(701);
				},
			]],
			655 => ['name' => 'function value parameter return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['dependency_type'] = $this->s->generated;
					$this->s->stay(656);
				},
			]],
			656 => ['name' => 'function value parameter exit', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = [
						'dependency_name' => $this->s->result['dependency_name'] ?? null,
						'dependency_type' => $this->s->result['dependency_type']
					];
					$this->s->pop();
				},
			]],















			701 => ['name' => 'type adt start', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(798);
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->stay(797);
				}
			]],
			798 => ['name' => 'union intersection check', 'transitions' => [
				T::union->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['union_left'] = $this->s->generated;
					$this->s->push(799);
					$this->s->move(797);
				},
				T::intersection->name => function(LT $token) {
					$this->s->result = [];
					$this->s->result['intersection_left'] = $this->s->generated;
					$this->s->push(796);
					$this->s->move(797);
				},
				'' => function(LT $token) {
					$this->s->pop();
				},
			]],
			796 => ['name' => 'intersection return', 'transitions' => [
				T::intersection->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->intersectionType(
						$this->s->result['intersection_left'],
						$this->s->generated
					);
					$this->s->stay(798);
				},
				'' => function(LT $token) {
					$intersectionLeft = $this->s->result['intersection_left'];
					$this->s->pop();
					$this->s->generated = $this->nodeBuilder->intersectionType(
						$intersectionLeft,
						$this->s->generated
					);
				},
			]],
			799 => ['name' => 'union return', 'transitions' => [
				T::union->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->unionType(
						$this->s->result['union_left'],
						$this->s->generated
					);
					$this->s->stay(798);
				},
				'' => function(LT $token) {
					$unionLeft = $this->s->result['union_left'];
					$this->s->pop();
					$this->s->generated = $this->nodeBuilder->unionType(
						$unionLeft,
						$this->s->generated
					);
				},
			]],
			509 => ['name' => 'type impure ? return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->impureType(
						$this->s->generated
					);
					$this->s->pop();
				}
			]],

			797 => ['name' => 'type start', 'transitions' => [
				T::arithmetic_op_multiply->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->push(509);
					$this->s->move(701);
				},
				T::type_proxy_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$type = substr($token->patternMatch->text, 1);
					$this->s->result['typeName'] = $type;
					$this->s->state = match($type) {
						'Integer' => 710,
						'Real' => 720,
						'String' => 730,
						'Array' => 740,
						'Set' => 940,
						'Map' => 750,
						'Type' => 760,
						'Impure' => 765,
						'Mutable' => 770,
						'Result' => 780,
						'Error' => 775,
						'Shape' => 930,
						'Any', 'Nothing', 'Boolean', 'True', 'False', 'Null',
						'MutableValue', 'EnumerationValue' => 702,
						default => 789
					};
					$this->s->i++;
				},
				T::type_keyword->name => function(LT $token) {
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->result['typeName'] = $token->patternMatch->text;
					$this->s->state = match($token->patternMatch->text) {
						'Integer' => 710,
						'Real' => 720,
						'String' => 730,
						'Array' => 740,
						'Set' => 940,
						'Map' => 750,
						'Shape' => 930,
						'Type' => 760,
						'Impure' => 765,
						'Mutable' => 770,
						'Error' => 775,
						'Result' => 780,
						'Any', 'Nothing', 'Boolean', 'True', 'False', 'Null',
						'MutableValue', 'EnumerationValue' => 702,
						default => 790
					};
					$this->s->i++;
				},
				T::sequence_start->name => -935, //Shape<T>
				T::empty_tuple->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType([]);
					$this->s->moveAndPop();
				},
				T::empty_record->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType([]);
					$this->s->moveAndPop();
				},
				T::call_start->name => 703,
				T::lambda_param->name => -901,
				T::tuple_start->name => function(LT $token) {
					$this->s->result['compositeValues'] = [];
					$this->s->result['startPosition'] = $token->sourcePosition;
					$this->s->move(812);
				},
			]],
			703 => ['name' => 'type open bracket', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(704);
					$this->s->stay(701);
				}
			]],
			704 => ['name' => 'type close bracket', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$g = $this->s->generated;

					//Temporary hack fixing (A|B) to include the brackets
					if ($this->s->result['startPosition'] ?? null) {
						if ($g instanceof UnionTypeNode) {
							$this->s->generated = new \Walnut\Lang\Implementation\AST\Node\Type\UnionTypeNode(
								new SourceLocation(
									$g->sourceLocation->moduleName,
									$this->s->result['startPosition'],
									$token->sourcePosition
								),
								$g->left,
								$g->right
							);
						} elseif ($g instanceof IntersectionTypeNode) {
							$this->s->generated = new \Walnut\Lang\Implementation\AST\Node\Type\IntersectionTypeNode(
								new SourceLocation(
									$g->sourceLocation->moduleName,
									$this->s->result['startPosition'],
									$token->sourcePosition
								),
								$g->left,
								$g->right
							);
						}
					}
					$this->s->moveAndPop();
				}
			]],
			702 => ['name' => 'type basic', 'transitions' => [
				'' => function(LT $token) {
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
						'EnumerationValue' => $this->nodeBuilder->metaTypeType(MetaTypeValue::EnumerationValue),
						'MutableValue' => $this->nodeBuilder->metaTypeType(MetaTypeValue::MutableValue)
					};
					$this->s->pop();
				},
			]],
			710 => ['name' => 'type integer', 'transitions' => [
				T::type_start->name => 711,
				T::tuple_start->name => 716,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerType();
					$this->s->pop();
				},
			]],
			711 => ['name' => 'type integer range start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['minValue'] = $token->patternMatch->text;
					$this->s->move(712);
				},
				T::integer_number->name => $c,
				T::range_dots->name => 713,
				T::call_start->name => $f = function(LT $token) {
					$this->s->push(718);
					$this->s->stay(850);
				},
				T::tuple_start->name => $f
			]],
			712 => ['name' => 'type integer range dots', 'transitions' => [
				T::range_dots->name => 713,
				'' => function(LT $token) {
					$value = $this->s->result['minValue'];
					$this->s->push(718);
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
					$this->s->stay(858);
				}
			]],
			713 => ['name' => 'type integer range end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['maxValue'] = $token->patternMatch->text;
					$this->s->move(714);
				},
				T::integer_number->name => $c,
				T::type_end->name => 715
			]],
			714 => ['name' => 'type integer type end', 'transitions' => [
				T::type_end->name => 715
			]],
			715 => ['name' => 'type integer return', 'transitions' => [
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
			716 => ['name' => 'type integer subset value', 'transitions' => [
				T::integer_number->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new Number($token->patternMatch->text);
					$this->s->move(717);
				},
				T::positive_integer_number->name => $c
			]],
			717 => ['name' => 'type integer subset separator', 'transitions' => [
				T::value_separator->name => 716,
				T::tuple_end->name => 715
			]],
			718 => ['name' => 'type integer full return', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->integerFullType(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],

			720 => ['name' => 'type real', 'transitions' => [
				T::type_start->name => 721,
				T::tuple_start->name => 726,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realType();
					$this->s->pop();
				},
			]],
			721 => ['name' => 'type real range start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['minValue'] = $token->patternMatch->text;
					$this->s->move(722);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::range_dots->name => 723,
				T::call_start->name => $f = function(LT $token) {
					$this->s->push(728);
					$this->s->stay(860);
				},
				T::tuple_start->name => $f
			]],
			722 => ['name' => 'type real range dots', 'transitions' => [
				T::range_dots->name => 723,
				'' => function(LT $token) {
					$value = $this->s->result['minValue'];
					$this->s->push(728);
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
					$this->s->stay(868);
				}
			]],
			723 => ['name' => 'type real range end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['maxValue'] = $token->patternMatch->text;
					$this->s->move(724);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::type_end->name => 725
			]],
			724 => ['name' => 'type real type end', 'transitions' => [
				T::type_end->name => 725
			]],
			725 => ['name' => 'type real return', 'transitions' => [
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
			726 => ['name' => 'type real subset value', 'transitions' => [
				T::real_number->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new Number($token->patternMatch->text);
					$this->s->move(727);
				},
				T::integer_number->name => $c,
				T::positive_integer_number->name => $c
			]],
			727 => ['name' => 'type real subset separator', 'transitions' => [
				T::value_separator->name => 726,
				T::tuple_end->name => 725
			]],
			728 => ['name' => 'type real full return', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->realFullType(
						$this->s->generated
					);
					$this->s->moveAndPop();
				},
			]],

			730 => ['name' => 'type string', 'transitions' => [
				T::type_start->name => 731,
				T::tuple_start->name => 736,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->stringType();
					$this->s->pop();
				},
			]],
			731 => ['name' => 'type string range start', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(732);
				},
				T::range_dots->name => 733
			]],
			732 => ['name' => 'type string range dots', 'transitions' => [
				T::range_dots->name => 733,
				T::type_end->name => function(LT $token) {
					$this->s->result['maxLength'] = $this->s->result['minLength'];
					$this->s->move(735);
				}
			]],
			733 => ['name' => 'type string range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(734);
				},
				T::type_end->name => 735
			]],
			734 => ['name' => 'type string type end', 'transitions' => [
				T::type_end->name => 735
			]],
			735 => ['name' => 'type string return', 'transitions' => [
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
			736 => ['name' => 'type string subset value', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = $this->escapeCharHandler->unescape(
						$token->patternMatch->text);
					$this->s->move(737);
				},
			]],
			737 => ['name' => 'type string subset separator', 'transitions' => [
				T::value_separator->name => 736,
				T::tuple_end->name => 735
			]],

			740 => ['name' => 'type array', 'transitions' => [
				T::type_start->name => 741,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->arrayType();
					$this->s->pop();
				},
			]],
			741 => ['name' => 'type array type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(742);
				},
				T::range_dots->name => 744,
				'' => function(LT $token) {
					$this->s->push(747);
					$this->s->stay(701);
				},
			]],
			742 => ['name' => 'type array range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(743);
				},
			]],
			743 => ['name' => 'type array range dots', 'transitions' => [
				T::range_dots->name => 744
			]],
			744 => ['name' => 'type array range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(745);
				},
				T::type_end->name => 746
			]],
			745 => ['name' => 'type array type end', 'transitions' => [
				T::type_end->name => 746
			]],
			746 => ['name' => 'type array return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->arrayType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			747 => ['name' => 'type array return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(748);
				}
			]],
			748 => ['name' => 'type array separator', 'transitions' => [
				T::value_separator->name => 749,
				T::type_end->name => 746
			]],
			749 => ['name' => 'type array type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(742);
				},
				T::range_dots->name => 744,
			]],

			750 => ['name' => 'type map', 'transitions' => [
				T::type_start->name => 751,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mapType();
					$this->s->pop();
				},
			]],
			751 => ['name' => 'type map type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(752);
				},
				T::range_dots->name => 754,
				'' => function(LT $token) {
					$this->s->push(757);
					$this->s->stay(701);
				},
			]],
			752 => ['name' => 'type map range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(753);
				}
			]],
			753 => ['name' => 'type map range dots', 'transitions' => [
				T::range_dots->name => 754
			]],
			754 => ['name' => 'type map range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(755);
				},
				T::type_end->name => 756
			]],
			755 => ['name' => 'type map type end', 'transitions' => [
				T::type_end->name => 756
			]],
			756 => ['name' => 'type map return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mapType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			757 => ['name' => 'type map return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(758);
				}
			]],
			758 => ['name' => 'type map separator', 'transitions' => [
				T::value_separator->name => 759,
				T::type_end->name => 756
			]],
			759 => ['name' => 'type map type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(752);
				},
				T::range_dots->name => 754,
			]],

			760 => ['name' => 'type type', 'transitions' => [
				T::type_start->name => 761,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			761 => ['name' => 'type type type', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					if (in_array($token->patternMatch->text, [
						'Function', 'Tuple', 'Record', 'Union', 'Intersection', 'Atom', 'Enumeration',
						'EnumerationSubset', 'EnumerationValue', 'IntegerSubset', 'RealSubset', 'StringSubset',
						'Data', 'Open', 'Sealed', 'Alias', 'Named', 'MutableValue'
					], true)) {
						$this->s->result['type'] = $this->nodeBuilder->metaTypeType(
							MetaTypeValue::from($token->patternMatch->text)
						);
						$this->s->move(763);
						return;
					}
					$this->s->push(762);
					$this->s->stay(701);
				},
				'' => function(LT $token) {
					$this->s->push(762);
					$this->s->stay(701);
				},
			]],
			762 => ['name' => 'type type return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(763);
				}
			]],
			763 => ['name' => 'type type separator', 'transitions' => [
				T::type_end->name => 764
			]],
			764 => ['name' => 'type type return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->typeType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],

			765 => ['name' => 'type impure', 'transitions' => [
				T::type_start->name => 766,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->impureType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			766 => ['name' => 'type impure type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(767);
					$this->s->stay(701);
				},
			]],
			767 => ['name' => 'type impure return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(768);
				}
			]],
			768 => ['name' => 'type impure separator', 'transitions' => [
				T::type_end->name => 769
			]],
			769 => ['name' => 'type impure return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->impureType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			770 => ['name' => 'type mutable', 'transitions' => [
				T::type_start->name => 771,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			771 => ['name' => 'type mutable type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(772);
					$this->s->stay(701);
				},
			]],
			772 => ['name' => 'type mutable return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(773);
				}
			]],
			773 => ['name' => 'type mutable separator', 'transitions' => [
				T::type_end->name => 774
			]],
			774 => ['name' => 'type mutable return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->mutableType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],

			775 => ['name' => 'type error', 'transitions' => [
				T::type_start->name => 776,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->nothingType,
						$this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			776 => ['name' => 'type error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(777);
					$this->s->stay(701);
				},
			]],
			777 => ['name' => 'type error return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(778);
				}
			]],
			778 => ['name' => 'type error separator', 'transitions' => [
				T::type_end->name => 779
			]],
			779 => ['name' => 'type error return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->nothingType,
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],

			780 => ['name' => 'type result', 'transitions' => [
				T::type_start->name => 781,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->nodeBuilder->anyType,
						$this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			781 => ['name' => 'type result type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(782);
					$this->s->stay(701);
				},
			]],
			782 => ['name' => 'type result return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(783);
				}
			]],
			783 => ['name' => 'type result separator', 'transitions' => [
				T::type_end->name => function(LT $token) {
					$this->s->result['error_type'] = $this->nodeBuilder->anyType;
					$this->s->stay(786);
				},
				T::value_separator->name => 784
			]],
			784 => ['name' => 'type result error type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(785);
					$this->s->stay(701);
				},
			]],
			785 => ['name' => 'type result error return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['error_type'] = $this->s->generated;
					$this->s->stay(786);
				}
			]],
			786 => ['name' => 'type result separator', 'transitions' => [
				T::type_end->name => 787
			]],
			787 => ['name' => 'type result return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->resultType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						$this->s->result['error_type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],

			789 => ['name' => 'type proxy basic', 'transitions' => [
				T::tuple_start->name => 791,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->proxyType(
						new TypeNameIdentifier($this->s->result['typeName'])
					);
					$this->s->pop();
				},
			]],
			790 => ['name' => 'type basic', 'transitions' => [
				T::tuple_start->name => 791,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->namedType(
						new TypeNameIdentifier($this->s->result['typeName'])
					);
					$this->s->pop();
				},
			]],
			791 => ['name' => 'type enum subset value', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->result['subsetValues'] ??= [];
					$this->s->result['subsetValues'][] = new EnumValueIdentifier($token->patternMatch->text);
					$this->s->move(792);
				},
				T::var_keyword->name => $c
			]],
			792 => ['name' => 'type string subset separator', 'transitions' => [
				T::value_separator->name => 791,
				T::tuple_end->name => 793
			]],
			793 => ['name' => 'type string return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->enumerationSubsetType(
						new TypeNameIdentifier($this->s->result['typeName']),
						$this->s->result['subsetValues']
					);
					$this->s->pop();
				},
			]],

			810 => ['name' => 'module level empty tuple', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->nodeBuilder->tupleType([])
						)
					);
					$this->s->move(102);
				}
			]],
			811 => ['name' => 'module level empty record', 'transitions' => [
				T::expression_separator->name => function(LT $token) {
					$this->nodeBuilder->definition(
						$this->s->generated = $this->nodeBuilder->addAlias(
							new TypeNameIdentifier($this->s->result['typeName']),
							$this->nodeBuilder->recordType([])
						)
					);
					$this->s->move(102);
				}
			]],
			812 => ['name' => 'module level tuple or record', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['first_token'] = new LT(
						$token->rule,
						new PatternMatch(
							$this->escapeCharHandler->unescape( $token->patternMatch->text)
						),
						$token->sourcePosition
					);
					$this->s->move(813);
				},
				T::type_keyword->name => $t = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(813);
				},
				T::arithmetic_op_multiply->name => $t,
				T::sequence_start->name => $t, //Shape<T>
				T::word->name => $c = function(LT $token) {
					$this->s->result['first_token'] = $token;
					$this->s->move(814);
				},
				T::var_keyword->name => $c,
				T::type->name => $c,
				T::rest_type->name => 830,
				T::default_match->name => 824,
				T::colon->name => 834,
				T::tuple_start->name => function(LT $token) {
					$this->s->stay(826);
				},
			]],
			813 => ['name' => 'module level tuple or record decider', 'transitions' => [
				T::colon->name => function(LT $token) {
					$this->s->move(815);
				},
				'' => function(LT $token) {
					$this->s->back(826);
				},
			]],
			814 => ['name' => 'module level record colon', 'transitions' => [
				T::colon->name => 815,
			]],
			815 => ['name' => 'module level record value type', 'transitions' => [
				T::optional_key->name => function(LT $token) {
					$this->s->result['current_key'] ??=
                        $this->s->result['first_token']->patternMatch->text;
					$this->s->push(840);
					$this->s->move(701);
				},
				'' => function(LT $token) {
					$this->s->result['current_key'] ??=
                        $this->s->result['first_token']->patternMatch->text;
					if ($token->rule->tag === T::type_keyword->name && $token->patternMatch->text === 'OptionalKey') {
						$this->s->move(835);
						return;
					}
					$this->s->push(816);
					$this->s->stay(701);
				},
			]],
			840 => ['name' => 'type optional ? return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->stay(817);
				}
			]],
			835 => ['name' => 'type optional key', 'transitions' => [
				T::type_start->name => 836,
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType(
							$this->nodeBuilder->anyType
						);
					$this->s->stay(817);
				},
			]],
			836 => ['name' => 'type optional type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(837);
					$this->s->stay(701);
				},
			]],
			837 => ['name' => 'type optional return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(838);
				}
			]],
			838 => ['name' => 'type optional separator', 'transitions' => [
				T::type_end->name => 839
			]],
			839 => ['name' => 'type optional return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] =
						$this->nodeBuilder->optionalKeyType($this->s->generated);
					$this->s->stay(817);
				},
			]],

			816 => ['name' => 'module level record value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][$this->s->result['current_key']] = $this->s->generated;
					$this->s->stay(817);
				}
			]],
			817 => ['name' => 'module level record value separator', 'transitions' => [
				T::tuple_end->name => 818,
				T::value_separator->name => 819,
			]],
			818 => ['name' => 'module level record value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			819 => ['name' => 'module level record key', 'transitions' => [
				T::string_value->name => function(LT $token) {
					$this->s->result['current_key'] = $this->escapeCharHandler->unescape( $token->patternMatch->text);
					$this->s->move(814);
				},
				T::word->name => $c = function(LT $token) {
					$this->s->result['current_key'] = $token->patternMatch->text;
					$this->s->move(814);
				},
				T::var_keyword->name => $c,
				T::type_keyword->name => $c,
				T::type->name => $c,
				T::mutable->name => $c,
				T::val->name => $c,
				T::true->name => $c,
				T::false->name => $c,
				T::null->name => $c,
				T::rest_type->name => 820,
				T::default_match->name => 824,
			]],
			820 => ['name' => 'module level record rest', 'transitions' => [
				T::tuple_end->name => 823,
				'' => function(LT $token) {
					$this->s->push(821);
					$this->s->stay(701);
				},
			]],
			821 => ['name' => 'module level record value rest return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['restType'] = $this->s->generated;
					$this->s->stay(822);
				}
			]],
			822 => ['name' => 'module level record value end', 'transitions' => [
				T::tuple_end->name => 823
			]],
			823 => ['name' => 'module level record value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->recordType(
						$this->s->result['compositeValues'],
						$this->s->result['restType'] ?? $this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			824 => ['name' => 'module level record key is type name', 'transitions' => [
				T::type_keyword->name => function(LT $token) {
					$typeName = $token->patternMatch->text;
					$recordKey = lcfirst($typeName);
					$this->s->result['compositeValues'][$recordKey] =
						$this->nodeBuilder->namedType(
							new TypeNameIdentifier($typeName)
						);
					$this->s->move(817);
				},
			]],
			/*825 => ['name' => 'module level record value rest return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['restType'] = $this->s->generated;
					$this->s->stay(822);
				}
			]],*/

			826 => ['name' => 'module level tuple value type', 'transitions' => [
				T::rest_type->name => 830,
				'' => function(LT $token) {
					$this->s->push(827);
					$this->s->stay(701);
				},
			]],
			827 => ['name' => 'module level tuple value return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['compositeValues'][] = $this->s->generated;
					$this->s->stay(828);
				}
			]],
			828 => ['name' => 'module level tuple value separator', 'transitions' => [
				T::tuple_end->name => 829,
				T::value_separator->name => 826,
			]],
			829 => ['name' => 'module level tuple value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType(
						$this->s->result['compositeValues']
					);
					$this->s->pop();
				},
			]],
			830 => ['name' => 'module level tuple rest', 'transitions' => [
				T::tuple_end->name => 833,
				'' => function(LT $token) {
					$this->s->push(831);
					$this->s->stay(701);
				},
			]],
			831 => ['name' => 'module level tuple value rest return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['restType'] = $this->s->generated;
					$this->s->stay(832);
				}
			]],
			832 => ['name' => 'module level tuple value end', 'transitions' => [
				T::tuple_end->name => 833
			]],
			833 => ['name' => 'module level tuple value return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->tupleType(
						$this->s->result['compositeValues'],
						$this->s->result['restType'] ?? $this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			834 => ['name' => 'module level tuple value end', 'transitions' => [
				T::rest_type->name => 820
			]],


			850 => ['name' => 'integer interval init', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'] = [];
					$this->s->stay(851);
				}
			]],
			851 => ['name' => 'integer interval start', 'transitions' => [
				T::call_start->name => 852,
				T::tuple_start->name => 853,
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(857);
				},
				T::integer_number->name => $c,
			]],
			852 => ['name' => 'integer interval open start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(854);
				},
				T::integer_number->name => $c,
				T::range_dots->name => function(LT $token) {
					$this->s->result['intervalStart'] = MinusInfinity::value;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(855);
				},
			]],
			853 => ['name' => 'integer interval closed start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->move(854);
				},
				T::integer_number->name => $c,
			]],
			854 => ['name' => 'integer interval dots', 'transitions' => [
				T::range_dots->name => 855
			]],
			855 => ['name' => 'integer interval end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->move(856);
				},
				T::integer_number->name => $c,
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEnd'] = PlusInfinity::value;
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(857);
				},
			]],
			856 => ['name' => 'integer interval bracket', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(857);
				},
				T::tuple_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(857);
				},
			]],
			857 => ['name' => 'integer interval add', 'transitions' => [
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
					$this->s->stay(858);
				}
			]],
			858 => ['name' => 'integer interval separator', 'transitions' => [
				T::value_separator->name => 851,
				'' => function(LT $token) {
					$this->s->generated = $this->s->result['intervals'];
					$this->s->pop();
				}
			]],

			860 => ['name' => 'real interval init', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['intervals'] = [];
					$this->s->stay(861);
				}
			]],
			861 => ['name' => 'real interval start', 'transitions' => [
				T::call_start->name => 862,
				T::tuple_start->name => 863,
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(867);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
			]],
			862 => ['name' => 'real interval open start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(864);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::range_dots->name => function(LT $token) {
					$this->s->result['intervalStart'] = MinusInfinity::value;
					$this->s->result['intervalStartIsInclusive'] = false;
					$this->s->move(865);
				},
			]],
			863 => ['name' => 'real interval closed start', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalStart'] = $token->patternMatch->text;
					$this->s->result['intervalStartIsInclusive'] = true;
					$this->s->move(864);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
			]],
			864 => ['name' => 'real interval dots', 'transitions' => [
				T::range_dots->name => 865
			]],
			865 => ['name' => 'real interval end', 'transitions' => [
				T::positive_integer_number->name => $c = function(LT $token) {
					$this->s->result['intervalEnd'] = $token->patternMatch->text;
					$this->s->move(866);
				},
				T::integer_number->name => $c,
				T::real_number->name => $c,
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEnd'] = PlusInfinity::value;
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(867);
				},
			]],
			866 => ['name' => 'real interval bracket', 'transitions' => [
				T::call_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = false;
					$this->s->move(867);
				},
				T::tuple_end->name => function(LT $token) {
					$this->s->result['intervalEndIsInclusive'] = true;
					$this->s->move(867);
				},
			]],
			867 => ['name' => 'real interval add', 'transitions' => [
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
					$this->s->stay(868);
				}
			]],
			868 => ['name' => 'real interval separator', 'transitions' => [
				T::value_separator->name => 861,
				'' => function(LT $token) {
					$this->s->generated = $this->s->result['intervals'];
					$this->s->pop();
				}
			]],

			930 => ['name' => 'type shape', 'transitions' => [
				T::type_start->name => 931,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->shapeType(
						$this->nodeBuilder->anyType
					);
					$this->s->pop();
				},
			]],
			931 => ['name' => 'type shape type', 'transitions' => [
				'' => function(LT $token) {
					$this->s->push(932);
					$this->s->stay(701);
				},
			]],
			932 => ['name' => 'type shape return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(933);
				}
			]],
			933 => ['name' => 'type shape separator', 'transitions' => [
				T::type_end->name => 934
			]],
			934 => ['name' => 'type shape return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->shapeType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
					);
					$this->s->pop();
				},
			]],
			935 => ['name' => 'name shape type in quotes', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(936);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::tuple_start->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
			]],
			936 => ['name' => 'type shape separator', 'transitions' => [
				T::sequence_end->name => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->shapeType(
						$this->s->generated,
					);
					$this->s->moveAndPop();
				}
			]],


			940 => ['name' => 'type set', 'transitions' => [
				T::type_start->name => 941,
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setType();
					$this->s->pop();
				},
			]],
			941 => ['name' => 'type set type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(942);
				},
				T::range_dots->name => 944,
				'' => function(LT $token) {
					$this->s->push(947);
					$this->s->stay(701);
				},
			]],
			942 => ['name' => 'type set range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['minLength'] = $token->patternMatch->text;
					$this->s->move(943);
				},
			]],
			943 => ['name' => 'type set range dots', 'transitions' => [
				T::range_dots->name => 944
			]],
			944 => ['name' => 'type set range end', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->result['maxLength'] = $token->patternMatch->text;
					$this->s->move(945);
				},
				T::type_end->name => 946
			]],
			945 => ['name' => 'type set type end', 'transitions' => [
				T::type_end->name => 946
			]],
			946 => ['name' => 'type set return', 'transitions' => [
				'' => function(LT $token) {
					$this->s->generated = $this->nodeBuilder->setType(
						$this->s->result['type'] ?? $this->nodeBuilder->anyType,
						isset($this->s->result['minLength']) ? new Number($this->s->result['minLength']) : new Number(0),
						isset($this->s->result['maxLength']) ? new Number($this->s->result['maxLength']) : PlusInfinity::value
					);
					$this->s->pop();
				},
			]],
			947 => ['name' => 'type set return point', 'transitions' => [
				'' => function(LT $token) {
					$this->s->result['type'] = $this->s->generated;
					$this->s->stay(948);
				}
			]],
			948 => ['name' => 'type set separator', 'transitions' => [
				T::value_separator->name => 949,
				T::type_end->name => 946
			]],
			949 => ['name' => 'type set type or range', 'transitions' => [
				T::positive_integer_number->name => function(LT $token) {
					$this->s->stay(942);
				},
				T::range_dots->name => 944,
			]],



			901 => ['name' => 'function type parameter type', 'transitions' => [
				T::type_keyword->name => $c = function(LT $token) {
					$this->s->push(902);
					$this->s->stay(701);
				},
				T::arithmetic_op_multiply->name => $c,
				T::sequence_start->name => $c, //Shape<T>
				T::tuple_start->name => $c,
				T::empty_tuple->name => $c,
				T::empty_record->name => $c,
			]],
			902 => ['name' => 'function type parameter return', 'transitions' => [
				T::lambda_return->name => function(LT $token) {
					$this->s->result['parameter'] = $this->s->generated;
					$this->s->move(903);
				},
				'' => function(LT $token) {
					$this->s->result['parameter'] = $this->s->generated;
					$this->s->generated = $this->nodeBuilder->anyType;
					$this->s->stay(904);
				},
			]],
			903 => ['name' => 'function type return type', 'transitions' => [
				T::type_proxy_keyword->name => $c = function(LT $token) {
					$this->s->push(904);
					$this->s->stay(701);
				},
				T::sequence_start->name => $c, //Shape<T>
				T::type_keyword->name => $c,
				T::tuple_start->name => $c,
				T::arithmetic_op_multiply->name => $c,
			]],
			904 => ['name' => 'function type return return', 'transitions' => [
				'' => function(LT $token) {
					$return = $this->s->generated;
					$this->s->generated = $this->nodeBuilder->functionType(
						$this->s->result['parameter'] ?? $this->nodeBuilder->anyType,
						$return ?? $this->nodeBuilder->anyType
					);
					$this->s->pop();
				}
			]]
		];		
	}

	private function noErrorMethodCall(bool $useGenerated): void {
		$parameter = $this->s->result['expression_left'];
		//TEMP
		/*if ($this->s->result['is_no_external_error'] ?? false) {
			$parameter = $this->nodeBuilder->noExternalError($parameter);
		} elseif ($this->s->result['is_no_error'] ?? false) {
			$parameter = $this->nodeBuilder->noError($parameter);
		}*/
		$this->s->generated = $this->nodeBuilder->methodCall(
			$parameter,
			new MethodNameIdentifier($this->s->result['method_name']),
			$useGenerated ? $this->nodeBuilder->sequence([$this->s->generated]) :
				$this->nodeBuilder->constant($this->nodeBuilder->nullValue)
		);
		if ($this->s->result['is_no_external_error'] ?? false) {
			$this->s->generated = $this->nodeBuilder->noExternalError($this->s->generated);
		} elseif ($this->s->result['is_no_error'] ?? false) {
			$this->s->generated = $this->nodeBuilder->noError($this->s->generated);
		}
	}
}
