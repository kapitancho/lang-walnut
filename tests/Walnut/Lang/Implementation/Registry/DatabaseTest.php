<?php

namespace Walnut\Lang\Test\Implementation\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class DatabaseTest extends BaseProgramTestHelper {

    public function testDatabaseQueryParameterType(): void {

        $this->assertEquals(
			'[dsn: String]',
	        (string)$this->typeRegistry->complex->data(
				new TypeNameIdentifier('DatabaseConnection')
	        )->valueType
        );
        $this->assertEquals(
			'(String|Integer|Boolean|Null)',
	        (string)$this->typeRegistry->complex->alias(
				new TypeNameIdentifier('DatabaseValue')
	        )->aliasedType
        );
        $this->assertEquals(
			'Map<DatabaseValue>',
	        (string)$this->typeRegistry->complex->alias(
				new TypeNameIdentifier('DatabaseQueryDataRow')
	        )->aliasedType
        );
        $this->assertEquals(
			'Array<DatabaseQueryDataRow>',
	        (string)$this->typeRegistry->complex->alias(
				new TypeNameIdentifier('DatabaseQueryResult')
	        )->aliasedType
        );
        $this->assertEquals(
			'(Array<DatabaseValue>|Map<DatabaseValue>)',
	        (string)$this->typeRegistry->complex->alias(
				new TypeNameIdentifier('DatabaseQueryBoundParameters')
	        )->aliasedType
        );
        $this->assertEquals(
			"[\n\tquery: String<1..>,\n\tboundParameters: DatabaseQueryBoundParameters\n]",
	        (string)$this->typeRegistry->complex->alias(
				new TypeNameIdentifier('DatabaseQueryCommand')
	        )->aliasedType
        );
        $this->assertEquals(
			"[\n\tquery: String<1..>,\n\tboundParameters: DatabaseQueryBoundParameters,\n\terror: String\n]",
	        (string)$this->typeRegistry->complex->data(
				new TypeNameIdentifier('DatabaseQueryFailure')
	        )->valueType
        );
        $this->assertEquals(
			'[connection: DatabaseConnection]',
	        (string)$this->typeRegistry->complex->sealed(
				new TypeNameIdentifier('DatabaseConnector')
	        )->valueType
        );
    }

}