<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Function\UnionMethodCall;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MainMethodRegistry implements MethodFinder {
	use BaseType;

	private NestedMethodRegistry $registry;

	public function __construct(
		NativeCodeTypeMapper           $nativeCodeTypeMapper,
		MethodRegistry                 $customMethodRegistry,
		private array                  $lookupNamespaces
	) {
		$this->registry = new NestedMethodRegistry(
			$customMethodRegistry,
			new NativeCodeMethodRegistry(
				$nativeCodeTypeMapper,
				$this->lookupNamespaces
			)
		);
	}

	public function methodForType(Type $targetType, MethodNameIdentifier $methodName): Method|UnknownMethod {
		if ($methodName->identifier === 'as') {
			$methodName = new MethodNameIdentifier('castAs');
		}
		$baseType = $this->toBaseType($targetType);
		if ($baseType instanceof IntersectionType) {
			$methods = [];
			foreach($baseType->types as $type) {
				$method = $this->methodForType($type, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$type, $method];
				}
			}
			if (count($methods) > 0) {
				$unique = [];
				foreach($methods as $method) {
					$uKey = $method[1] instanceof NativeMethod ? $method[1]::class : (string)$method[0];
					$unique[$uKey] = $method;
				}
				if (count($unique) === 1) {
					return $unique[array_key_first($unique)][1];//$methods[0][1];
				}
				$method = $this->registry->methodForType($targetType, $methodName);
				return $method instanceof Method ? $method :
					// @codeCoverageIgnoreStart
					throw new AnalyserException(
					sprintf(
						"Cannot call method '%s' on type '%s': ambiguous method",
						$methodName,
						$targetType
					)
					// @codeCoverageIgnoreEnd
				);
			}
		}
		if ($baseType instanceof UnionType) {
			$methods = [];
			foreach($baseType->types as $type) {
				$method = $this->methodForType($type, $methodName);
				if ($method instanceof Method) {
					$methods[] = [$type, $method];
				} else {
					$methods = [];
					break;
				}
			}
			if (count($methods) > 0) {
				return new UnionMethodCall($methods);
			}
		}
		return $this->registry->methodForType($targetType, $methodName);
	}

	public function methodForValue(Value $target, MethodNameIdentifier $methodName): Method|UnknownMethod {
		$method = $this->methodForType($target->type, $methodName);
		if ($method instanceof Method) {
			return $method;
		}
		return UnknownMethod::value;
	}
}