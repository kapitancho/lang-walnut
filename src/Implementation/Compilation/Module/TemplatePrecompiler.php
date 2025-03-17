<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;

final readonly class TemplatePrecompiler implements CodePrecompiler {
	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		$sourceCode = preg_replace('^<!-- (.*?) %% (.*?) -->^', <<<CODE
		module $moduleName %% \$tpl, $2:
			
		$1 ==> Template @ UnableToRenderTemplate %% [~TemplateRenderer] :: {
			output = Template(mutable{String, ''});
			e = ^String :: output->value->APPEND(#);
			h = ^String :: output->value->APPEND(#->htmlEscape);
			-->
								
		CODE, $sourceCode);
		$sourceCode .= <<<CODE
		    <!--
		    output
		};
		CODE;
		$sourceCode = preg_replace('/<!--\[(.*?)]-->/s', "<!-- e({ $1 }->asString);\n -->", $sourceCode);
		$sourceCode = preg_replace('/<!--\{(.*?)}-->/s', "<!-- h({ $1 }->asString);\n -->", $sourceCode);
		$sourceCode = preg_replace('/<!--%(.*?)%-->/s', "<!-- e(%templateRenderer=>render($1));\n -->", $sourceCode);
		$sourceCode = preg_replace_callback('/-->(.*?)<!--/s', function($matches) {
			$modifiedString = str_replace(['\\', "\n", "'"], ['\\\\', '\n', '\`'], $matches[1]);
			return "e('$modifiedString');\n";
		}, $sourceCode);
		return $sourceCode;
	}
}