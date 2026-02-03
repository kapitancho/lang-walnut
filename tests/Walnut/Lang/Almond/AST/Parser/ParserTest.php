<?php /** @noinspection TypeUnsafeComparisonInspection */

namespace Walnut\Lang\Test\Almond\AST\Parser;

use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddMethodNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Implementation\Builder\NodeBuilderFactory;
use Walnut\Lang\Almond\AST\Implementation\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\SetExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\AnyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ArrayTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\BooleanTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\BytesTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\FalseTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\FunctionTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ImpureTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerFullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MapTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MutableTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NamedTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NothingTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NumberIntervalEndpointNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ProxyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealFullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RecordTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ResultTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\SetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ShapeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\StringTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TrueTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TupleTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TypeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\UnionTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\AtomValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\BytesValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\EnumerationValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\ErrorValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\FalseValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\FunctionValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\IntegerValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\MutableValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\NullValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\RealValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\RecordValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\SetValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TrueValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TupleValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TypeValueNode;
use Walnut\Lang\Almond\AST\Implementation\Parser\ParserState;
use Walnut\Lang\Almond\AST\Implementation\Parser\ParserStateRunner;
use Walnut\Lang\Almond\AST\Implementation\Parser\TransitionLogger;
use Walnut\Lang\Almond\AST\Implementation\Parser\WalexLexerAdapter;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lib\Walex\SourcePosition;

class ParserTest extends TestCase {

	protected WalexLexerAdapter $walexLexerAdapter;
	protected ParserStateRunner $parserStateRunner;
	protected TransitionLogger $transitionLogger;

	protected function setUp(): void {
		parent::setUp();

		$this->walexLexerAdapter = new WalexLexerAdapter();
		$this->parserStateRunner = new ParserStateRunner(
			$this->transitionLogger = new TransitionLogger(),
			new NodeBuilderFactory()
		);
	}

	/** @return array{0: ParserState, 1: ModuleNode} */
	protected function runParserTest(string $sourceCode, int $initialState = 100): array {
		$s = new ParserState();
		$s->push(-1);
		$s->state = $initialState;
		return [$s, $this->parserStateRunner->run(
			new SourceLocator(
				'test',
				$this->walexLexerAdapter->tokensFromSource($sourceCode),
				$s
			)
		)];
	}

	public function testSimple(): void {
		[, $moduleNode] = $this->runParserTest('module test: A = Integer;');
		self::assertEquals('test', $moduleNode->moduleName);
		self::assertCount(0, $moduleNode->moduleDependencies);
		self::assertInstanceOf(AddAliasTypeNode::class, $moduleNode->definitions[0]);
		self::assertEquals('A', $moduleNode->definitions[0]->name->name);
		self::assertInstanceOf(IntegerTypeNode::class, $moduleNode->definitions[0]->aliasedType);
	}

	public function testParseError(): void {
		try {
			$this->runParserTest('module test: A = Integer{;');
			$this->fail("There should be a parser error for this code.");
		} catch (ParserException $ex) {
			$this->assertStringContainsString('No transition found', $ex->getMessage());
		}
	}

	public function testSimpleWithDependencies(): void {
		[, $moduleNode] = $this->runParserTest('module test %% dep1, dep2: A = Integer;');
		self::assertCount(2, $moduleNode->moduleDependencies);
		self::assertEquals('dep1', $moduleNode->moduleDependencies[0]);
		self::assertInstanceOf(AddAliasTypeNode::class, $moduleNode->definitions[0]);
		self::assertEquals('A', $moduleNode->definitions[0]->name->name);
		self::assertInstanceOf(IntegerTypeNode::class, $moduleNode->definitions[0]->aliasedType);
	}

	#[DataProvider('moduleLevelDefinitions')]
	public function testModuleLevelDefinitions(string $code, string $className, callable|null $checker = null): void {
		[$s, $moduleNode] = $this->runParserTest($code, 101);
		self::assertInstanceOf($className, $moduleNode->definitions[0]);
		if (is_callable($checker)) {
			self::assertTrue($checker($moduleNode->definitions[0]));
		}
		$this->checkPosition($code, $s->generated);
	}

	public static function moduleLevelDefinitions(): iterable {
		$p = new SourcePosition(0, 0, 0);
		$l = new SourceLocation('m', $p, $p);

		yield ['MyAtom := ();', AddAtomTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyAtom'))];
		yield ['MyEnum := (v1, v2);', AddEnumerationTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyEnum')) &&
			count($d->values) === 2 && $d->values[0]->equals(new EnumerationValueNameNode($l, 'v1')) && $d->values[1]->equals(new EnumerationValueNameNode($l, 'v2'))];
		yield ['MyOpen := #Null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyOpen := #Null :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MyOpen := #Null @ E :: null;', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyOpen')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameNode($l, 'E'))];
		yield ['MyOpen := #[Real];', AddOpenTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyOpen')) &&
			$d->valueType instanceOf TupleTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed := $Null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MySealed := $Null :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType === null];
		yield ['MySealed := $Null @ E :: null;', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MySealed')) &&
			$d->valueType instanceOf NullTypeNode && $d->constructorBody !== null && $d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameNode($l, 'E'))];
		yield ['MySealed := $[a: Integer];', AddSealedTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MySealed')) &&
			$d->valueType instanceOf RecordTypeNode && $d->constructorBody === null && $d->errorType === null];
		yield ['MyAlias = Null;', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyAlias')) &&
			$d->aliasedType instanceOf NullTypeNode];
		yield ['MyAlias = [];', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyAlias')) &&
			$d->aliasedType instanceOf TupleTypeNode && count($d->aliasedType->types) === 0];
		yield ['MyAlias = [:];', AddAliasTypeNode::class, fn($d) => $d->name->equals(new TypeNameNode($l, 'MyAlias')) &&
			$d->aliasedType instanceOf RecordTypeNode && count($d->aliasedType->types) === 0];

		yield ['A(P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) %% D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) %% ~D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name->equals(new VariableNameNode($l, 'd')) &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) %% d: D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name->equals(new VariableNameNode($l, 'd')) &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) %% [D] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof TupleTypeNode && count($d->dependency->type->types) === 1 &&
			$d->dependency->type->types[0] instanceof NamedTypeNode && $d->dependency->type->types[0]->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(P) @ C :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameNode($l, 'C'))];
		yield ['A(P) @ C %% D :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->errorType instanceof NamedTypeNode && $d->errorType->name->equals(new TypeNameNode($l, 'C'))];
		yield ['A(~P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(p: P) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A(p) :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof AnyTypeNode && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A() :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof TupleTypeNode && count($d->parameter->type->types) === 0 && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[P] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof TupleTypeNode && count($d->parameter->type->types) === 1 &&
			$d->parameter->type->types[0] instanceof NamedTypeNode && $d->parameter->type->types[0]->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[P, Q] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof TupleTypeNode && count($d->parameter->type->types) === 2 &&
			$d->parameter->type->types[0] instanceof NamedTypeNode && $d->parameter->type->types[0]->name->equals(new TypeNameNode($l, 'P')) &&
			$d->parameter->type->types[1] instanceof NamedTypeNode && $d->parameter->type->types[1]->name->equals(new TypeNameNode($l, 'Q')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[:] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof RecordTypeNode && count($d->parameter->type->types) === 0 && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[a: P] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof RecordTypeNode && count($d->parameter->type->types) === 1 &&
			$d->parameter->type->types['a'] instanceof NamedTypeNode && $d->parameter->type->types['a']->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];
		yield ['A[a: P, b: Q] :: null;', AddConstructorMethodNode::class, fn(AddConstructorMethodNode $d) =>
			$d->typeName->equals(new TypeNameNode($l, 'A')) &&
			$d->parameter->type instanceof RecordTypeNode && count($d->parameter->type->types) === 2 &&
			$d->parameter->type->types['a'] instanceof NamedTypeNode && $d->parameter->type->types['a']->name->equals(new TypeNameNode($l, 'P')) &&
			$d->parameter->type->types['b'] instanceof NamedTypeNode && $d->parameter->type->types['b']->name->equals(new TypeNameNode($l, 'Q')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->errorType instanceof NothingTypeNode];



		yield ['A->asB(^P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];


		yield ['A->asB(^ ~P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^ ~P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^ ~P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^ ~P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];


		yield ['A->asB(^p: P) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^p: P) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^p: P => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^p: P => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^p: P => B) %% ~D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name->name === 'd' &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^p: P => B) %% d: D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NamedTypeNode && $d->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name->name === 'd' &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];


		yield ['A->asB(^p) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof AnyTypeNode && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB(^p) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof AnyTypeNode && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(^p => B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof AnyTypeNode && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(^p => B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof AnyTypeNode && $d->parameter->name->equals(new VariableNameNode($l, 'p')) &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];


		yield ['A->asB() :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof AnyTypeNode];
		yield ['A->asB() %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof AnyTypeNode];

		yield ['A->asB(=> B) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(=> Result<B, C>) :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];
		yield ['A->asB(=> B) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A->asB(=> Result<B, C>) %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];

		yield ['A ==> B :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A ==> B @ C :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];
		yield ['A ==> B %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A ==> B %% ~D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name instanceof VariableNameNode && $d->dependency->name->equals(new VariableNameNode($l, 'd')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A ==> B %% d: D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name instanceof VariableNameNode && $d->dependency->name->equals(new VariableNameNode($l, 'd')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A ==> B %% [D] :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof TupleTypeNode && count($d->dependency->type->types) === 1 &&
			$d->dependency->type->types[0] instanceof NamedTypeNode && $d->dependency->type->types[0]->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['A ==> B @ C %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'A' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];

		yield ['==> B :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B'))];
		yield ['==> B @ C :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];
		yield ['==> B %% D;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B')) &&
			$d->functionBody->expression instanceof MethodCallExpressionNode
		];
		yield ['==> B %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'B')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof NullValueNode
		];
		yield ['==> B @ C %% D :: null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asB')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NamedTypeNode && $d->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->returnType instanceof ResultTypeNode &&
				$d->returnType->returnType instanceof NamedTypeNode && $d->returnType->returnType->name->equals(new TypeNameNode($l, 'B')) &&
				$d->returnType->errorType instanceof NamedTypeNode && $d->returnType->errorType->name->equals(new TypeNameNode($l, 'C'))
		];


		yield ['=> null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asCliEntryPoint')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->dependency->type instanceof NothingTypeNode &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'CliEntryPoint'))];
		yield ['%% D => null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asCliEntryPoint')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'CliEntryPoint')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof FunctionValueNode &&
			$d->functionBody->expression->value->dependency->type instanceof NamedTypeNode && $d->functionBody->expression->value->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->functionBody->expression->value->dependency->name === null
		];
		yield ['%% ~D => null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asCliEntryPoint')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'CliEntryPoint')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof FunctionValueNode &&
			$d->functionBody->expression->value->dependency->type instanceof NamedTypeNode && $d->functionBody->expression->value->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->functionBody->expression->value->dependency->name instanceof VariableNameNode && $d->functionBody->expression->value->dependency->name->equals(new VariableNameNode($l, 'd'))
		];
		yield ['%% d: D => null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asCliEntryPoint')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'CliEntryPoint')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof FunctionValueNode &&
			$d->functionBody->expression->value->dependency->type instanceof NamedTypeNode && $d->functionBody->expression->value->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$d->functionBody->expression->value->dependency->name instanceof VariableNameNode && $d->functionBody->expression->value->dependency->name->equals(new VariableNameNode($l, 'd'))
		];
		yield ['%% [D] => null;', AddMethodNode::class, fn(AddMethodNode $d) =>
			$d->targetType instanceof TypeNameNode && $d->targetType->name === 'DependencyContainer' &&
			$d->methodName->equals(new MethodNameNode($l, 'asCliEntryPoint')) &&
			$d->parameter->type instanceof NullTypeNode && $d->parameter->name === null &&
			$d->returnType instanceof NamedTypeNode && $d->returnType->name->equals(new TypeNameNode($l, 'CliEntryPoint')) &&
			$d->functionBody->expression instanceof ConstantExpressionNode &&
			$d->functionBody->expression->value instanceof FunctionValueNode &&
			$d->functionBody->expression->value->dependency->type instanceof TupleTypeNode && count($d->functionBody->expression->value->dependency->type->types) === 1 &&
			$d->functionBody->expression->value->dependency->type->types[0] instanceof NamedTypeNode && $d->functionBody->expression->value->dependency->type->types[0]->name->equals(new TypeNameNode($l, 'D')) &&
			$d->dependency->name === null
		];

	}

	private function checkPosition(string $code, SourceNode $node): void {
		return; //TODO

		$n2 = strlen($code);
		$n1 = $n2 - 1;
		$f = $node->sourceLocation->startPosition->offset;
		$t = $node->sourceLocation->endPosition->offset;
		$codeX = $f >= $t ? $code :
			substr($code, 0, $f) .
			"\033[35;1;4m" . substr($code, $f, $t - $f) . "\033[0m" .
			substr($code, $t);
		self::assertEquals(
			"test(1:1:0-1:$n2:$n1)",
			$node->sourceLocation->jsonSerialize(),
			sprintf("The position of %s is not correct.", $codeX)
		);
	}

	#[DataProvider('expressions')]
	public function testParseExpressions(string $code, string $className, callable|null $checker = null): void {
		[$s] = $this->runParserTest($code, 3000);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
		//$this->checkPosition($code, $s->generated);
	}

	public static function expressions(): iterable {
		$p = new SourcePosition(0, 0, 0);
		$l = new SourceLocation('m', $p, $p);

		yield ['[]', TupleExpressionNode::class, fn(TupleExpressionNode $e) => count($e->values) === 0];
		yield ['[;]', SetExpressionNode::class, fn(SetExpressionNode $e) => count($e->values) === 0];
		yield ['[:]', RecordExpressionNode::class, fn(RecordExpressionNode $e) => count($e->values) === 0];
		yield ['4', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof IntegerValueNode && (string)$e->value->value === '4'];
		yield ['C.value', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) =>
			$e->value instanceof EnumerationValueNode && $e->value->name->equals(new TypeNameNode($l, 'C')) &&
			$e->value->enumValue->equals(new EnumerationValueNameNode($l, 'value'))
		];
		yield ['x', VariableNameExpressionNode::class, fn(VariableNameExpressionNode $e) => $e->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['$', VariableNameExpressionNode::class, fn(VariableNameExpressionNode $e) => $e->variableName->equals(new VariableNameNode($l, '$'))];
		yield ['x = y', VariableAssignmentExpressionNode::class, fn(VariableAssignmentExpressionNode $e) =>
			$e->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['mutable{A, x}', MutableExpressionNode::class, fn(MutableExpressionNode $e) =>
			$e->type instanceof NamedTypeNode && $e->type->name->equals(new TypeNameNode($l, 'A')) &&
			$e->value instanceof VariableNameExpressionNode && $e->value->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['{x; y}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 2 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->expressions[1] instanceof VariableNameExpressionNode && $e->expressions[1]->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['{x}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 1 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['{x;}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) =>
			count($e->expressions) === 1 &&
			$e->expressions[0] instanceof VariableNameExpressionNode && $e->expressions[0]->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['{}', SequenceExpressionNode::class, fn(SequenceExpressionNode $e) => count($e->expressions) === 0];
		yield ['[x, y]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->values[1] instanceof VariableNameExpressionNode && $e->values[1]->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['[$]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameNode($l, '$'))
		];
		yield ['[x]', TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ["['x'];", TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof ConstantExpressionNode && $e->values[0]->value instanceof StringValueNode && $e->values[0]->value->value === 'x'
		];
		yield ["['x', 'y'];", TupleExpressionNode::class, fn(TupleExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof ConstantExpressionNode && $e->values[0]->value instanceof StringValueNode && $e->values[0]->value->value === 'x' &&
			$e->values[1] instanceof ConstantExpressionNode && $e->values[1]->value instanceof StringValueNode && $e->values[1]->value->value === 'y'
		];
		yield ['[x; y]', SetExpressionNode::class, fn(SetExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->values[1] instanceof VariableNameExpressionNode && $e->values[1]->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['[x;]', SetExpressionNode::class, fn(SetExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values[0] instanceof VariableNameExpressionNode && $e->values[0]->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['[a: x, b: y]', RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->values['b'] instanceof VariableNameExpressionNode && $e->values['b']->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["['a': x, 'b': y];", RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 2 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->values['b'] instanceof VariableNameExpressionNode && $e->values['b']->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['[a: x]', RecordExpressionNode::class, fn(RecordExpressionNode $e) =>
			count($e->values) === 1 &&
			$e->values['a'] instanceof VariableNameExpressionNode && $e->values['a']->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['=> x', ReturnExpressionNode::class, fn(ReturnExpressionNode $e) =>
			$e->returnedExpression instanceof VariableNameExpressionNode && $e->returnedExpression->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['x?', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof VariableNameExpressionNode && $e->targetExpression->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['x*?', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof VariableNameExpressionNode && $e->targetExpression->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['x.y', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->propertyName === 'y'
		];
		yield ["x.'y';", PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->propertyName === 'y'
		];
		yield ['x.0', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->propertyName === 0
		];
		yield ['x.y.z', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof PropertyAccessExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->target->propertyName === 'y' &&
			$e->propertyName === 'z'
		];
		yield ['@x', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'Error')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameNode($l, 'x'))];
		yield [':: x', ScopedExpressionNode::class, fn(ScopedExpressionNode $e) =>
			$e->targetExpression instanceof VariableNameExpressionNode && $e->targetExpression->variableName->equals(new VariableNameNode($l, 'x'))];

		yield ['C', ConstantExpressionNode::class, fn(ConstantExpressionNode $e) => $e->value instanceof AtomValueNode &&
			$e->value->name->equals(new TypeNameNode($l, 'C'))];
		yield ['C()', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['C(x)', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['C[]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 0];
		yield ['C[x]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['C[x, y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["C['x'];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x'];
		yield ["C['x', 'y'];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
						$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x' &&
						$e->parameter->values[1] instanceof ConstantExpressionNode && $e->parameter->values[1]->value instanceof StringValueNode && $e->parameter->values[1]->value->value === 'y'];

		yield ['C[;]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 0];
		yield ['C[x;]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['C[x; y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];

		yield ['C[:]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 0];
		yield ['C[a: x]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['C[a: x, b: y]', ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["C['a': x];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ["C['a': x, 'b': y];", ConstructorCallExpressionNode::class, fn(ConstructorCallExpressionNode $e) => $e->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];

		yield ['C[a: 1].a', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof ConstructorCallExpressionNode &&
			$e->target->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->target->parameter instanceof RecordExpressionNode &&
			count($e->target->parameter->values) === 1 &&
			$e->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->target->parameter->values['a']->value->value === '1' &&
			$e->propertyName === 'a'];
		yield ['C[a: 1]->a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof ConstructorCallExpressionNode &&
			$e->target->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->target->parameter instanceof RecordExpressionNode &&
			count($e->target->parameter->values) === 1 &&
			$e->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->target->parameter->values['a']->value->value === '1' &&
			$e->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['C[a: 1]->a?', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof ConstructorCallExpressionNode &&
			$e->targetExpression->target->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->targetExpression->target->parameter instanceof RecordExpressionNode &&
			count($e->targetExpression->target->parameter->values) === 1 &&
			$e->targetExpression->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->targetExpression->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->targetExpression->target->parameter->values['a']->value->value === '1' &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['C[a: 1]->a*?', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof ConstructorCallExpressionNode &&
			$e->targetExpression->target->typeName->equals(new TypeNameNode($l, 'C')) &&
			$e->targetExpression->target->parameter instanceof RecordExpressionNode &&
			count($e->targetExpression->target->parameter->values) === 1 &&
			$e->targetExpression->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->targetExpression->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->targetExpression->target->parameter->values['a']->value->value === '1' &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['f()', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['f(x)', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['f[]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 0];
		yield ['f[x]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['f[x, y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["f['x'];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x'];
		yield ["f['x', 'y'];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
						$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x' &&
						$e->parameter->values[1] instanceof ConstantExpressionNode && $e->parameter->values[1]->value instanceof StringValueNode && $e->parameter->values[1]->value->value === 'y'];

		yield ['f[;]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 0];
		yield ['f[x;]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['f[x; y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];

		yield ['f[:]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 0];
		yield ['f[a: x]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['f[a: x, b: y]', FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["f['a': x];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ["f['a': x, 'b': y];", FunctionCallExpressionNode::class, fn(FunctionCallExpressionNode $e) => $e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'f')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];

		yield ['c[a: 1].a', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof FunctionCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode &&
			$e->target->target->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->target->parameter instanceof RecordExpressionNode &&
			count($e->target->parameter->values) === 1 &&
			$e->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->target->parameter->values['a']->value->value === '1' &&
			$e->propertyName === 'a'];
		yield ['c[a: 1]->a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof FunctionCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode &&
			$e->target->target->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->target->parameter instanceof RecordExpressionNode &&
			count($e->target->parameter->values) === 1 &&
			$e->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->target->parameter->values['a']->value->value === '1' &&
			$e->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['c[a: 1]->a?', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof FunctionCallExpressionNode &&
			$e->targetExpression->target->target instanceof VariableNameExpressionNode &&
			$e->targetExpression->target->target->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->targetExpression->target->parameter instanceof RecordExpressionNode &&
			count($e->targetExpression->target->parameter->values) === 1 &&
			$e->targetExpression->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->targetExpression->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->targetExpression->target->parameter->values['a']->value->value === '1' &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['c[a: 1]->a*?', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof FunctionCallExpressionNode &&
			$e->targetExpression->target->target instanceof VariableNameExpressionNode &&
			$e->targetExpression->target->target->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->targetExpression->target->parameter instanceof RecordExpressionNode &&
			count($e->targetExpression->target->parameter->values) === 1 &&
			$e->targetExpression->target->parameter->values['a'] instanceof ConstantExpressionNode &&
			$e->targetExpression->target->parameter->values['a']->value instanceof IntegerValueNode &&
			(string)$e->targetExpression->target->parameter->values['a']->value->value === '1' &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'a')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];

		yield ['a->b?', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['a->b*?', NoExternalErrorExpressionNode::class, fn(NoExternalErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];
		yield ['+a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'unaryPlus')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['-a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'unaryMinus')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['~a', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'unaryBitwiseNot')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['!a', BooleanNotExpressionNode::class, fn(BooleanNotExpressionNode $e) =>
			$e->expression instanceof VariableNameExpressionNode && $e->expression->variableName->equals(new VariableNameNode($l, 'a'))];
		yield ['a + x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'binaryPlus')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['a - x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryMinus'))];
		yield ['a * x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryMultiply'))];
		yield ['a / x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryDivide'))];
		yield ['a // x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryIntegerDivide'))];
		yield ['a % x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryModulo'))];
		yield ['a ** x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryPower'))];
		yield ['a & x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryBitwiseAnd'))];
		yield ['a | x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryBitwiseOr'))];
		yield ['a ^ x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryBitwiseXor'))];
		yield ['a < x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryLessThan'))];
		yield ['a <= x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryLessThanEqual'))];
		yield ['a > x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryGreaterThan'))];
		yield ['a >= x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryGreaterThanEqual'))];
		yield ['a != x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryNotEqual'))];
		yield ['a == x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryEqual'))];
		yield ['a || x', BooleanOrExpressionNode::class, fn(BooleanOrExpressionNode $e) =>
			$e->first instanceof VariableNameExpressionNode && $e->first->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->second instanceof VariableNameExpressionNode && $e->second->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['a && x', BooleanAndExpressionNode::class, fn(BooleanAndExpressionNode $e) =>
			$e->first instanceof VariableNameExpressionNode && $e->first->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->second instanceof VariableNameExpressionNode && $e->second->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['a ^^ x', BooleanXorExpressionNode::class, fn(BooleanXorExpressionNode $e) =>
			$e->first instanceof VariableNameExpressionNode && $e->first->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->second instanceof VariableNameExpressionNode && $e->second->variableName->equals(new VariableNameNode($l, 'x'))
		];
		yield ['a ?? x', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) => $e->methodName->equals(new MethodNameNode($l, 'binaryOrElse'))];

		yield ['a *> (null)', NoErrorExpressionNode::class, fn(NoErrorExpressionNode $e) =>
			$e->targetExpression instanceof MethodCallExpressionNode &&
			$e->targetExpression->target instanceof VariableNameExpressionNode && $e->targetExpression->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->targetExpression->methodName->equals(new MethodNameNode($l, 'errorAsExternal')) &&
			$e->targetExpression->parameter instanceof ConstantExpressionNode && $e->targetExpression->parameter->value instanceof NullValueNode];

		yield ['a->b->c', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->target->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->target->parameter instanceof ConstantExpressionNode && $e->target->parameter->value instanceof NullValueNode &&
			$e->methodName->equals(new MethodNameNode($l, 'c')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b(c)->d', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->target->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->target->parameter instanceof VariableNameExpressionNode && $e->target->parameter->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->methodName->equals(new MethodNameNode($l, 'd')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b.c', PropertyAccessExpressionNode::class, fn(PropertyAccessExpressionNode $e) =>
			$e->target instanceof MethodCallExpressionNode &&
			$e->target->target instanceof VariableNameExpressionNode && $e->target->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->target->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->target->parameter instanceof ConstantExpressionNode && $e->target->parameter->value instanceof NullValueNode &&
			$e->propertyName === 'c'];

		yield ['a->b', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b(null)', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof ConstantExpressionNode && $e->parameter->value instanceof NullValueNode];
		yield ['a->b(x)', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof VariableNameExpressionNode && $e->parameter->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['a->b[]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 0];
		yield ['a->b[x.y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof PropertyAccessExpressionNode &&
			$e->parameter->values[0]->target instanceof VariableNameExpressionNode && $e->parameter->values[0]->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[0]->propertyName === 'y'
		];

		yield ['a->b[x]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['a->b[x, y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["a->b['x'];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x'];
		yield ["a->b['x', 'y'];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof TupleExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof ConstantExpressionNode && $e->parameter->values[0]->value instanceof StringValueNode && $e->parameter->values[0]->value->value === 'x' &&
			$e->parameter->values[1] instanceof ConstantExpressionNode && $e->parameter->values[1]->value instanceof StringValueNode && $e->parameter->values[1]->value->value === 'y'];
		yield ['a->b[;]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 0];
		yield ['a->b[x;]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['a->b[x; y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof SetExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values[0] instanceof VariableNameExpressionNode && $e->parameter->values[0]->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values[1] instanceof VariableNameExpressionNode && $e->parameter->values[1]->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ['a->b[:]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 0];
		yield ['a->b[a: x]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ['a->b[a: x, b: y]', MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];
		yield ["a->b['a': x];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 1 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x'))];
		yield ["a->b['a': x, 'b': y];", MethodCallExpressionNode::class, fn(MethodCallExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->methodName->equals(new MethodNameNode($l, 'b')) &&
			$e->parameter instanceof RecordExpressionNode && count($e->parameter->values) === 2 &&
			$e->parameter->values['a'] instanceof VariableNameExpressionNode && $e->parameter->values['a']->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->parameter->values['b'] instanceof VariableNameExpressionNode && $e->parameter->values['b']->variableName->equals(new VariableNameNode($l, 'y'))];
		//more
		yield ['?whenIsError(x) { y }', MatchErrorExpressionNode::class, fn(MatchErrorExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->onError instanceof SequenceExpressionNode && count($e->onError->expressions) === 1 &&
			$e->onError->expressions[0] instanceof VariableNameExpressionNode && $e->onError->expressions[0]->variableName->equals(new VariableNameNode($l, 'y')) &&
			$e->else === null
		];
		yield ['?whenIsError(x) { y } ~ { z }', MatchErrorExpressionNode::class, fn(MatchErrorExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->onError instanceof SequenceExpressionNode && count($e->onError->expressions) === 1 &&
			$e->onError->expressions[0] instanceof VariableNameExpressionNode && $e->onError->expressions[0]->variableName->equals(new VariableNameNode($l, 'y')) &&
			$e->else instanceof SequenceExpressionNode && count($e->else->expressions) === 1 &&
			$e->else->expressions[0] instanceof VariableNameExpressionNode && $e->else->expressions[0]->variableName->equals(new VariableNameNode($l, 'z'))
		];
		yield ['?when(x) { y }', MatchIfExpressionNode::class, fn(MatchIfExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->then instanceof SequenceExpressionNode && count($e->then->expressions) === 1 &&
			$e->then->expressions[0] instanceof VariableNameExpressionNode && $e->then->expressions[0]->variableName->equals(new VariableNameNode($l, 'y')) &&
			$e->else instanceof ConstantExpressionNode && $e->else->value instanceof NullValueNode
		];
		yield ['?when(x) { y } ~ { z }', MatchIfExpressionNode::class, fn(MatchIfExpressionNode $e) =>
			$e->condition instanceof VariableNameExpressionNode && $e->condition->variableName->equals(new VariableNameNode($l, 'x')) &&
			$e->then instanceof SequenceExpressionNode && count($e->then->expressions) === 1 &&
			$e->then->expressions[0] instanceof VariableNameExpressionNode && $e->then->expressions[0]->variableName->equals(new VariableNameNode($l, 'y')) &&
			$e->else instanceof SequenceExpressionNode && count($e->else->expressions) === 1 &&
			$e->else->expressions[0] instanceof VariableNameExpressionNode && $e->else->expressions[0]->variableName->equals(new VariableNameNode($l, 'z'))
		];
		yield ['?whenTypeOf(x) { a: b }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b'))
		];
		yield ['?whenTypeOf(x) { a: b, c: d }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd'))
		];
		yield ['?whenTypeOf(x) { ~: e }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 0 && $e->default &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];
		yield ['?whenTypeOf(x) { a: b, c: d, ~: e }', MatchTypeExpressionNode::class, fn(MatchTypeExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->default instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd')) &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];
		yield ['?whenValueOf(x) { a: b }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b'))
		];
		yield ['?whenValueOf(x) { a: b, c: d }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd'))
		];
		yield ['?whenValueOf(x) { ~: e }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 0 && $e->default instanceof MatchExpressionDefaultNode &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];
		yield ['?whenValueOf(x) { a: b, c: d, ~: e }', MatchValueExpressionNode::class, fn(MatchValueExpressionNode $e) =>
			$e->target instanceof VariableNameExpressionNode && $e->target->variableName->equals(new VariableNameNode($l, 'x')) &&
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->default instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd')) &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];
		yield ['?whenIsTrue { a: b }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 1 && $e->pairs[0] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b'))
		];
		yield ['?whenIsTrue { a: b, c: d }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd'))
		];
		yield ['?whenIsTrue { ~: e }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 0 && $e->default instanceof MatchExpressionDefaultNode &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];
		yield ['?whenIsTrue { a: b, c: d, ~: e }', MatchTrueExpressionNode::class, fn(MatchTrueExpressionNode $e) =>
			count($e->pairs) === 2 && $e->pairs[0] instanceof MatchExpressionPairNode && $e->pairs[1] instanceof MatchExpressionPairNode && $e->default instanceof MatchExpressionDefaultNode &&
			$e->pairs[0]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[0]->matchExpression->variableName->equals(new VariableNameNode($l, 'a')) &&
			$e->pairs[0]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[0]->valueExpression->variableName->equals(new VariableNameNode($l, 'b')) &&
			$e->pairs[1]->matchExpression instanceof VariableNameExpressionNode && $e->pairs[1]->matchExpression->variableName->equals(new VariableNameNode($l, 'c')) &&
			$e->pairs[1]->valueExpression instanceof VariableNameExpressionNode && $e->pairs[1]->valueExpression->variableName->equals(new VariableNameNode($l, 'd')) &&
			$e->default->valueExpression instanceof VariableNameExpressionNode && $e->default->valueExpression->variableName->equals(new VariableNameNode($l, 'e'))
		];

		yield ['var{a} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames[0]->equals(new VariableNameNode($l, 'a')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{a, b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames[0]->equals(new VariableNameNode($l, 'a')) &&
			$e->variableNames[1]->equals(new VariableNameNode($l, 'b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{a, b, c} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 3 &&
			$e->variableNames[0]->equals(new VariableNameNode($l, 'a')) &&
			$e->variableNames[1]->equals(new VariableNameNode($l, 'b')) &&
			$e->variableNames[2]->equals(new VariableNameNode($l, 'c')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{~a} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'a')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{a: x} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{'a': x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{is: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['is']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{true: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['true']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{false: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['false']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{null: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['null']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{var: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['var']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{type: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['type']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{mutable: x} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 1 &&
			$e->variableNames['mutable']->equals(new VariableNameNode($l, 'x')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{~a, ~b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'a')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{a: x, ~b} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'b')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{~a, b: z} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'a')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{a: x, b: z} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{'a': x, 'b': z} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 2 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'z')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ["var{'a': x, is: z, true: i, false: j, null: k, type: l, var: m, mutable: n} = y", MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 8 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'x')) &&
			$e->variableNames['is']->equals(new VariableNameNode($l, 'z')) &&
			$e->variableNames['true']->equals(new VariableNameNode($l, 'i')) &&
			$e->variableNames['false']->equals(new VariableNameNode($l, 'j')) &&
			$e->variableNames['null']->equals(new VariableNameNode($l, 'k')) &&
			$e->variableNames['type']->equals(new VariableNameNode($l, 'l')) &&
			$e->variableNames['var']->equals(new VariableNameNode($l, 'm')) &&
			$e->variableNames['mutable']->equals(new VariableNameNode($l, 'n')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
		yield ['var{~a, ~b, ~c} = y', MultiVariableAssignmentExpressionNode::class, fn(MultiVariableAssignmentExpressionNode $e) =>
			count($e->variableNames) === 3 &&
			$e->variableNames['a']->equals(new VariableNameNode($l, 'a')) &&
			$e->variableNames['b']->equals(new VariableNameNode($l, 'b')) &&
			$e->variableNames['c']->equals(new VariableNameNode($l, 'c')) &&
			$e->assignedExpression instanceof VariableNameExpressionNode && $e->assignedExpression->variableName->equals(new VariableNameNode($l, 'y'))
		];
	}

	#[DataProvider('types')]
	public function testParseTypes(string $code, string $className, callable|null $checker = null): void {
		//$code .= ';';
		[$s] = $this->runParserTest($code, 4000);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
		//echo $this->transitionLogger;
		$this->checkPosition($code, $s->generated);
	}

	public static function types(): iterable {
		$p = new SourcePosition(0, 0, 0);
		$l = new SourceLocation('m', $p, $p);

		yield ['Nothing', NothingTypeNode::class];
		yield ['\\Nothing', NothingTypeNode::class];
		yield ['True', TrueTypeNode::class];
		yield ['\\True', TrueTypeNode::class];
		yield ['False', FalseTypeNode::class];
		yield ['\\False', FalseTypeNode::class];
		yield ['Boolean', BooleanTypeNode::class];
		yield ['(Boolean)', BooleanTypeNode::class];
		yield ['\\Boolean', BooleanTypeNode::class];
		yield ['Any', AnyTypeNode::class];
		yield ['\\Any', AnyTypeNode::class];
		yield ['Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['\\Integer', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Integer<5..>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Integer<..-8>', IntegerTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Integer<-5..8>', IntegerTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Integer[5]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0]->value == new Number('5')];
		yield ['Integer[5, -8]', IntegerSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0]->value == new Number('5') && $t->values[1]->value == new Number('-8')];
		yield ['Integer<[5..8], (..6)>', IntegerFullTypeNode::class, fn($t) => count($t->intervals) === 2 && $t->intervals[0]->start == new NumberIntervalEndpointNode(new Number(5), true) && $t->intervals[0]->end == new NumberIntervalEndpointNode(new Number(8), true) && $t->intervals[1]->start === MinusInfinity::value && $t->intervals[1]->end == new NumberIntervalEndpointNode(new Number(6), false)];
		yield ['Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['\\Real', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue === PlusInfinity::value];
		yield ['Real<5..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8')];
		yield ['Real<-5..8>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5') && $t->maxValue == new Number('8')];
		yield ['Real<5.14..>', RealTypeNode::class, fn($t) => $t->minValue == new Number('5.14') && $t->maxValue === PlusInfinity::value];
		yield ['Real<..-8.14>', RealTypeNode::class, fn($t) => $t->minValue === MinusInfinity::value && $t->maxValue == new Number('-8.14')];
		yield ['Real<-5.14..8.14>', RealTypeNode::class, fn($t) => $t->minValue == new Number('-5.14') && $t->maxValue == new Number('8.14')];
		yield ['Real[3.14]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0]->value == new Number('3.14')];
		yield ['Real[3.14, -8]', RealSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0]->value == new Number('3.14') && $t->values[1]->value == new Number('-8')];
		yield ['Real<[5.15..8], (..6)>', RealFullTypeNode::class, fn($t) => count($t->intervals) === 2 && $t->intervals[0]->start == new NumberIntervalEndpointNode(new Number('5.15'), true) && $t->intervals[0]->end == new NumberIntervalEndpointNode(new Number(8), true) && $t->intervals[1]->start === MinusInfinity::value && $t->intervals[1]->end == new NumberIntervalEndpointNode(new Number(6), false)];
		yield ['String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\String', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['String<5..>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['String<..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['String<5>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['String<5..8>', StringTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ["String['hello']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 1 && $t->values[0]->value == 'hello'];
		yield ["String['hello', 'world']", StringSubsetTypeNode::class, fn($t) => count($t->values) === 2 && $t->values[0]->value == 'hello' && $t->values[1]->value == 'world'];
		yield ['Bytes', BytesTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Bytes', BytesTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Bytes<5..>', BytesTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Bytes<..8>', BytesTypeNode::class, fn($t) => $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Bytes<5>', BytesTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['Bytes<5..8>', BytesTypeNode::class, fn($t) => $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Array', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<5>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['Array<5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, 5..>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Array<Boolean, ..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Array<Boolean, 5..8>', ArrayTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Map', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<5>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['Map<5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, 5..>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Map<Boolean, ..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Map<Boolean, 5..8>', MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ["Map<String['a', 'b', 'c']:Boolean>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value && $t->keyType instanceof StringSubsetTypeNode && count($t->keyType->values) === 3 && $t->keyType->values[0]->value == 'a' && $t->keyType->values[1]->value == 'b' && $t->keyType->values[2]->value == 'c'];
		yield ["Map<String['a', 'b', 'c']:Boolean, 5..>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value && $t->keyType instanceof StringSubsetTypeNode && count($t->keyType->values) === 3 && $t->keyType->values[0]->value == 'a' && $t->keyType->values[1]->value == 'b' && $t->keyType->values[2]->value == 'c'];
		yield ["Map<String['a', 'b', 'c']:Boolean, ..8>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8') && $t->keyType instanceof StringSubsetTypeNode && count($t->keyType->values) === 3 && $t->keyType->values[0]->value == 'a' && $t->keyType->values[1]->value == 'b' && $t->keyType->values[2]->value == 'c'];
		yield ["Map<String['a', 'b', 'c']:Boolean, 5..8>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8') && $t->keyType instanceof StringSubsetTypeNode && count($t->keyType->values) === 3 && $t->keyType->values[0]->value == 'a' && $t->keyType->values[1]->value == 'b' && $t->keyType->values[2]->value == 'c'];
		yield ["Map<String:Boolean>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value && $t->keyType instanceOf StringTypeNode && $t->keyType->minLength == new Number('0') && $t->keyType->maxLength === PlusInfinity::value];
		yield ["Map<String<2..>:Boolean, 5..>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value && $t->keyType instanceOf StringTypeNode && $t->keyType->minLength == new Number('2') && $t->keyType->maxLength === PlusInfinity::value];
		yield ["Map<String<..5>:Boolean, ..8>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8') && $t->keyType instanceOf StringTypeNode && $t->keyType->minLength == new Number('0') && $t->keyType->maxLength == new Number('5')];
		yield ["Map<String<2..5>:Boolean, 5..8>", MapTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8') && $t->keyType instanceOf StringTypeNode && $t->keyType->minLength == new Number('2') && $t->keyType->maxLength == new Number('5')];
		yield ['Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['\\Set', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<5>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('5')];
		yield ['Set<5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf AnyTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, 5..>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength === PlusInfinity::value];
		yield ['Set<Boolean, ..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('0') && $t->maxLength == new Number('8')];
		yield ['Set<Boolean, 5..8>', SetTypeNode::class, fn($t) => $t->itemType instanceOf BooleanTypeNode && $t->minLength == new Number('5') && $t->maxLength == new Number('8')];

		yield ['MyCustomType', NamedTypeNode::class, fn($t) => $t->name->equals(new TypeNameNode($l, 'MyCustomType'))];
		yield ['\\MyProxyType', ProxyTypeNode::class, fn($t) => $t->name->equals(new TypeNameNode($l, 'MyProxyType'))];
		yield ['Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['\\Type', TypeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Type<Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Type<Tuple>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::Tuple->value];
		yield ['Type<\\Enumeration>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::Enumeration->value];
		yield ['Type<\\MutableValue>', TypeTypeNode::class, fn($t) => $t->refType instanceOf MetaTypeTypeNode && $t->refType->value === MetaTypeValue::MutableValue->value];
		yield ['Type<Integer|Boolean>', TypeTypeNode::class, fn($t) => $t->refType instanceOf UnionTypeNode &&
			$t->refType->left instanceOf IntegerTypeNode && $t->refType->right instanceOf BooleanTypeNode];
		yield ['Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['\\Mutable', MutableTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Mutable<Boolean>', MutableTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['\\Shape', ShapeTypeNode::class, fn($t) => $t->refType instanceOf AnyTypeNode];
		yield ['Shape<Boolean>', ShapeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['{Boolean}', ShapeTypeNode::class, fn($t) => $t->refType instanceOf BooleanTypeNode];
		yield ['Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['\\Result', ResultTypeNode::class, fn($t) => $t->returnType instanceOf AnyTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Result<Null, Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NullTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['\\Error', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf AnyTypeNode];
		yield ['Error<Boolean>', ResultTypeNode::class, fn($t) => $t->returnType instanceOf NothingTypeNode && $t->errorType instanceOf BooleanTypeNode];
		yield ['Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['\\Impure', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		yield ['Impure<Boolean>', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['*Boolean', ImpureTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
		yield ['[]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[...]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[[Null]]', TupleTypeNode::class, fn($t) => count($t->types) === 1 &&
			$t->types[0] instanceof TupleTypeNode && count($t->types[0]->types) === 1 && $t->restType instanceOf NothingTypeNode &&
			$t->types[0]->types[0] instanceof NullTypeNode && $t->types[0]->restType instanceOf NothingTypeNode];
		yield ['[Null]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 1 && $t->types[0] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[Null, Any]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[Null, Any, ...]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[Null, Any, ... Boolean]', TupleTypeNode::class, fn($t) => count($t->types) === 2 && $t->types[0] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[:]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf NothingTypeNode];
		yield ['[: ...]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf AnyTypeNode];
		yield ['[: ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 0 && $t->restType instanceOf BooleanTypeNode];
		yield ['[~A]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NamedTypeNode && $t->types['a']->name->equals(new TypeNameNode($l, 'A')) && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 1 && $t->types['a'] instanceof NullTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf NothingTypeNode];
		yield ['[a: Null, b: Any, ...]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf AnyTypeNode];
		yield ['[a: Null, b: Any, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		yield ['[a: Null, b: Any, c: ?Null, d: OptionalKey<Null>, e: OptionalKey, ... Boolean]', RecordTypeNode::class, fn($t) => count($t->types) === 5 &&
			$t->types['a'] instanceof NullTypeNode &&
			$t->types['b'] instanceof AnyTypeNode &&
			$t->types['c'] instanceof OptionalKeyTypeNode && $t->types['c']->valueType instanceof NullTypeNode &&
			$t->types['d'] instanceof OptionalKeyTypeNode && $t->types['d']->valueType instanceof NullTypeNode &&
			$t->types['e'] instanceof OptionalKeyTypeNode && $t->types['e']->valueType instanceof AnyTypeNode &&
			$t->restType instanceOf BooleanTypeNode];
		yield ["['a': Null, 'b': Any, ... Boolean]", RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types['b'] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];

		yield ["Type<?String>", TypeTypeNode::class, fn(TypeTypeNode $t) => $t->refType instanceof OptionalKeyTypeNode && $t->refType->valueType instanceof StringTypeNode];
		yield ["Type<?String<4>>", TypeTypeNode::class, fn(TypeTypeNode $t) => $t->refType instanceof OptionalKeyTypeNode && $t->refType->valueType instanceof StringTypeNode];
		yield ["Type<OptionalKey>", TypeTypeNode::class, fn(TypeTypeNode $t) => $t->refType instanceof OptionalKeyTypeNode && $t->refType->valueType instanceof AnyTypeNode];
		yield ["Type<OptionalKey<String>>", TypeTypeNode::class, fn(TypeTypeNode $t) => $t->refType instanceof OptionalKeyTypeNode && $t->refType->valueType instanceof StringTypeNode];
		yield ["Type<OptionalKey< String<5> >>", TypeTypeNode::class, fn(TypeTypeNode $t) => $t->refType instanceof OptionalKeyTypeNode && $t->refType->valueType instanceof StringTypeNode];

		yield ['MyEnum[Value1]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameNode($l, 'MyEnum')) && count($v->values) === 1 &&
			$v->values[0]->equals(new EnumerationValueNameNode($l, 'Value1'))];
		yield ['MyEnum[Value1, Value2]', EnumerationSubsetTypeNode::class, fn($v) => $v->name->equals(new TypeNameNode($l, 'MyEnum')) && count($v->values) === 2 &&
			$v->values[0]->equals(new EnumerationValueNameNode($l, 'Value1')) && $v->values[1]->equals(new EnumerationValueNameNode($l, 'Value2'))];
		yield ['^ => Any', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];
		yield ['^Null => Any', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];
		yield ['^Null', FunctionTypeNode::class, fn($v) => $v->parameterType instanceOf NullTypeNode && $v->returnType instanceOf AnyTypeNode];

		yield ['Boolean|Null', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['(Boolean|Null)', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['Boolean|Null|Type', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['(Boolean|Null|Type)', UnionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		//yield ['Any|Nothing|Boolean|True|False|Null|MutableValue|EnumerationValue', UnionTypeNode::class];
		yield ['Boolean&Null', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['(Boolean&Null)', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof NullTypeNode];
		yield ['Boolean&Null&Type', IntersectionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];
		yield ['(Boolean&Null&Type)', IntersectionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode && $t->right instanceof TypeTypeNode];

		yield ['Boolean|(Null&Type)', UnionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof IntersectionTypeNode &&
			$t->right->left instanceof NullTypeNode && $t->right->right instanceof TypeTypeNode];
		yield ['Boolean&(Null|Type)', IntersectionTypeNode::class, fn($t) => $t->left instanceof BooleanTypeNode && $t->right instanceof UnionTypeNode &&
			$t->right->left instanceof NullTypeNode && $t->right->right instanceof TypeTypeNode];
		yield ['(Boolean|Null)&Type', IntersectionTypeNode::class, fn($t) => $t->left instanceof UnionTypeNode && $t->right instanceof TypeTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode];
		yield ['(Boolean&Null)|Type', UnionTypeNode::class, fn($t) => $t->left instanceof IntersectionTypeNode && $t->right instanceof TypeTypeNode &&
			$t->left->left instanceof BooleanTypeNode && $t->left->right instanceof NullTypeNode];

		yield ['^Boolean&*{Null}|Type=>^String=>Real', FunctionTypeNode::class, fn($t) =>
			$t->parameterType instanceof UnionTypeNode &&
			$t->parameterType->left instanceof IntersectionTypeNode &&
			$t->parameterType->left->left instanceof BooleanTypeNode &&
			$t->parameterType->left->right instanceof ImpureTypeNode &&
			$t->parameterType->left->right->valueType instanceof ShapeTypeNode &&
			$t->parameterType->left->right->valueType->refType instanceof NullTypeNode &&
			$t->parameterType->right instanceof TypeTypeNode &&
			$t->returnType instanceof FunctionTypeNode &&
			$t->returnType->parameterType instanceof StringTypeNode &&
			$t->returnType->returnType instanceof RealTypeNode
		];

		//yield ["['a': Null, 'b': Any, ... Boolean]", RecordTypeNode::class, fn($t) => count($t->types) === 2 && $t->types['a'] instanceof NullTypeNode && $t->types[1] instanceof AnyTypeNode && $t->restType instanceOf BooleanTypeNode];
		//yield ['OptionalKey', OptionalKeyTypeNode::class, fn($t) => $t->valueType instanceOf AnyTypeNode];
		//yield ['OptionalKey<Boolean>', OptionalKeyTypeNode::class, fn($t) => $t->valueType instanceOf BooleanTypeNode];
	}

	#[DataProvider('constants')]
	public function testParseConstants(string $code, string $className, callable|null $checker = null): void {
		[$s] = $this->runParserTest($code, 401);
		self::assertInstanceOf($className, $s->generated);
		if (is_callable($checker)) {
			self::assertTrue($checker($s->generated));
		}
		$this->checkPosition($code, $s->generated);
	}

	public static function constants(): iterable {
		$p = new SourcePosition(0, 0, 0);
		$l = new SourceLocation('m', $p, $p);

		yield ['42', IntegerValueNode::class, fn($v) => $v->value == new Number('42')];
		yield ['3.14', RealValueNode::class, fn($v) => $v->value == new Number('3.14')];
		yield ["'Hello'", StringValueNode::class, fn($v) => $v->value == 'Hello'];
		yield ['"Hello"', BytesValueNode::class, fn($v) => $v->value == 'Hello'];
		yield ['true', TrueValueNode::class];
		yield ['false', FalseValueNode::class];
		yield ['null', NullValueNode::class];
		yield ['[]', TupleValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[42]', TupleValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == new Number('42')];
		yield ["['a']", TupleValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == 'a'];
		yield ['[42, 3.14]', TupleValueNode::class, fn($v) => count($v->values) == 2 && $v->values[0]->value == new Number('42') && $v->values[1]->value == new Number('3.14')];
		yield ['[:]', RecordValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[a: 42]', RecordValueNode::class, fn($v) => count($v->values) == 1 && $v->values['a']->value == new Number('42')];
		yield ["[a: 42, 'b': 3.14]", RecordValueNode::class, fn($v) => count($v->values) == 2 && $v->values['a']->value == new Number('42') && $v->values['b']->value == new Number('3.14')];
		yield ["['a': 42, b: 3.14]", RecordValueNode::class, fn($v) => count($v->values) == 2 && $v->values['a']->value == new Number('42') && $v->values['b']->value == new Number('3.14')];
		yield ['[;]', SetValueNode::class, fn($v) => count($v->values) == 0];
		yield ['[42;]', SetValueNode::class, fn($v) => count($v->values) == 1 && $v->values[0]->value == new Number('42')];
		yield ['[42; 3.14]', SetValueNode::class, fn($v) => count($v->values) == 2 && $v->values[0]->value == new Number('42') && $v->values[1]->value == new Number('3.14')];
		yield ['@null', ErrorValueNode::class, fn($v) => $v->value instanceof NullValueNode];
		yield ['mutable{Integer, 0}', MutableValueNode::class, fn($v) => $v->type instanceof IntegerTypeNode && $v->value instanceof IntegerValueNode];
		yield ['type{Any}', TypeValueNode::class, fn($v) => $v->type instanceof AnyTypeNode];
		yield ['type{Atom}', TypeValueNode::class, fn($v) => $v->type instanceof MetaTypeTypeNode && $v->type->value === MetaTypeValue::Atom->name];
		yield ['type{[Any]}', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];
		yield ['type[Any]', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];
		yield ['`Any', TypeValueNode::class, fn($v) => $v->type instanceof AnyTypeNode];
		yield ['`Atom', TypeValueNode::class, fn($v) => $v->type instanceof MetaTypeTypeNode && $v->type->value === MetaTypeValue::Atom->name];
		yield ['`[Any]', TypeValueNode::class, fn($v) => $v->type instanceof TupleTypeNode && count($v->type->types) === 1 && $v->type->types[0] instanceof AnyTypeNode];

		yield ["`?String", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["`?String<4>", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["`OptionalKey", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof AnyTypeNode];
		yield ["`OptionalKey<String>", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["`OptionalKey<String<4>>", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["type{?String}", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["type{?String<4>}", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["type{OptionalKey}", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof AnyTypeNode];
		yield ["type{OptionalKey<String>}", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];
		yield ["type{OptionalKey<String<4>>}", TypeValueNode::class, fn($v) => $v->type instanceof OptionalKeyTypeNode && $v->type->valueType instanceof StringTypeNode];

		yield ['MyAtom', AtomValueNode::class, fn($v) => $v->name->equals(new TypeNameNode($l, 'MyAtom'))];
		yield ['MyEnum.Value', EnumerationValueNode::class, fn($v) => $v->name->equals(new TypeNameNode($l, 'MyEnum')) && $v->enumValue->equals(new EnumerationValueNameNode($l, 'Value'))];
		yield ['^ :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^ %% True :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof TrueTypeNode];
		yield ['^ => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^p :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^p %% True :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof TrueTypeNode];
		yield ['^p => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof AnyTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^ ~P => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof NamedTypeNode &&
									$v->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^ ~P :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof NamedTypeNode &&
									$v->parameter->type->name->equals(new TypeNameNode($l, 'P')) && $v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^p: Q => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof NamedTypeNode &&
									$v->parameter->type->name->equals(new TypeNameNode($l, 'Q')) && $v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^p: Q :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof NamedTypeNode &&
									$v->parameter->type->name->equals(new TypeNameNode($l, 'Q')) && $v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^ => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^Null => Any %% True :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof TrueTypeNode];
		yield ['^Null %% True :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof TrueTypeNode];
		yield ['^p: False => Any :: null', FunctionValueNode::class, fn($v) => $v->parameter->name->equals(new VariableNameNode($l, 'p')) && $v->parameter->type instanceof FalseTypeNode &&
									$v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];
		yield ['^[Any] :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof TupleTypeNode &&
									count($v->parameter->type->types) === 1 && $v->parameter->type->types[0] instanceof AnyTypeNode && $v->returnType instanceof AnyTypeNode && $v->dependency->type instanceof NothingTypeNode];

		yield ['^ %% D :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
			$v->returnType instanceof AnyTypeNode &&
			$v->dependency->type instanceof NamedTypeNode && $v->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$v->dependency->name === null
		];
		yield ['^ %% ~D :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
			$v->returnType instanceof AnyTypeNode &&
			$v->dependency->type instanceof NamedTypeNode && $v->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$v->dependency->name->equals(new VariableNameNode($l, 'd'))
		];
		yield ['^ %% d: D :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
			$v->returnType instanceof AnyTypeNode &&
			$v->dependency->type instanceof NamedTypeNode && $v->dependency->type->name->equals(new TypeNameNode($l, 'D')) &&
			$v->dependency->name->equals(new VariableNameNode($l, 'd'))
		];
		yield ['^ %% [D] :: null', FunctionValueNode::class, fn($v) => $v->parameter->name === null && $v->parameter->type instanceof NullTypeNode &&
			$v->returnType instanceof AnyTypeNode &&
			$v->dependency->type instanceof TupleTypeNode && count($v->dependency->type->types) === 1 &&
			$v->dependency->type->types[0] instanceof NamedTypeNode && $v->dependency->type->types[0]->name->equals(new TypeNameNode($l, 'D')) &&
			$v->dependency->name === null
		];

	}

}