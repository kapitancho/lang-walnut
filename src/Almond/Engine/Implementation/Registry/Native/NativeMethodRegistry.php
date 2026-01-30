<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NativeCodeTypeMapper as NativeCodeTypeMapperInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NativeMethodLoader as NativeMethodLoaderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NativeMethodRegistry as NativeMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

final readonly class NativeMethodRegistry implements NativeMethodRegistryInterface {

	public function __construct(
		private NativeCodeTypeMapperInterface $codeTypeMapper,
		private NativeMethodLoaderInterface $nativeMethodLoader
	) {}

	/** @return list<NativeMethod> */
	public function nativeMethods(Type $type, MethodName $methodName): array {
		$typeNames = $this->codeTypeMapper->getTypesFor($type);
		return $this->nativeMethodLoader->loadNativeMethods($typeNames, $methodName);
	}

}