<?php

namespace Walnut\Lang\Test\Implementation\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class DatabaseTest extends BaseProgramTestHelper {

    public function testDatabaseQueryParameterType(): void {

        $this->assertEquals(
			'[dsn: String]',
	        (string)$this->typeRegistry->subtype(
				new TypeNameIdentifier('DatabaseConnection')
	        )->baseType
        );
        $this->assertEquals(
			'(String|Integer|Boolean|Null)',
	        (string)$this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseValue')
	        )->aliasedType
        );
        $this->assertEquals(
			'Map<DatabaseValue>',
	        (string)$this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseQueryResultRow')
	        )->aliasedType
        );
        $this->assertEquals(
			'Array<DatabaseQueryResultRow>',
	        (string)$this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseQueryResult')
	        )->aliasedType
        );
        $this->assertEquals(
			'(Array<DatabaseValue>|Map<DatabaseValue>)',
	        (string)$this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseQueryBoundParameters')
	        )->aliasedType
        );
        $this->assertEquals(
			"[\n\tquery: String<1..>,\n\tboundParameters: DatabaseQueryBoundParameters\n]",
	        (string)$this->typeRegistry->alias(
				new TypeNameIdentifier('DatabaseQueryCommand')
	        )->aliasedType
        );
        $this->assertEquals(
			"[\n\tquery: String<1..>,\n\tboundParameters: DatabaseQueryBoundParameters,\n\terror: String\n]",
	        (string)$this->typeRegistry->sealed(
				new TypeNameIdentifier('DatabaseQueryFailure')
	        )->valueType
        );
        $this->assertEquals(
			'[connection: DatabaseConnection]',
	        (string)$this->typeRegistry->sealed(
				new TypeNameIdentifier('DatabaseConnector')
	        )->valueType
        );
    }

}