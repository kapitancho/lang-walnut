<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodLoader as NativeMethodLoaderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NativeMethodRegistry as NativeMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NativeCodeTypeMapper as NativeCodeTypeMapperInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

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