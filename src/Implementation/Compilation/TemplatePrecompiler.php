<?php

namespace Walnut\Lang\Implementation\Compilation;

final readonly class TemplatePrecompiler {
	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		$sourceCode = preg_replace('^<!-- (.*?) %% (.*?) -->^', <<<CODE
		module $moduleName %% tpl, $2:
			
		$1 ==> Template @ UnableToRenderTemplate %% [~TemplateRenderer] :: {
			output = Template(mutable{String, ''});
			e = ^String :: output->APPEND(#);
			h = ^String :: output->APPEND(#->htmlEscape);
			-->
								
		CODE, $sourceCode);
		$sourceCode .= <<<CODE
		    <!--
		    output
		};
		CODE;
		$sourceCode = preg_replace('/<!--\[(.*?)]-->/s', "<!-- e($1->asString);\n -->", $sourceCode);
		$sourceCode = preg_replace('/<!--\{(.*?)}-->/s', "<!-- h($1->asString);\n -->", $sourceCode);
		$sourceCode = preg_replace('/<!--%(.*?)%-->/s', "<!-- e(%templateRenderer=>render($1));\n -->", $sourceCode);
		$sourceCode = preg_replace('/-->(.*?)<!--/s', "e('$1');\n", $sourceCode);
		return $sourceCode;
	}
}