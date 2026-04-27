module $tpl:

UnableToRenderTemplate := $[type: Type, reason: ?String];
UnableToRenderTemplate ==> String ::
    'Unable to render template of type ' + $type->asString + '; Reason: ' + ($reason ?? 'unknown');

Template := #Mutable<String>;

TemplateRenderer := ();
TemplateRenderer->render(^view => Result<String, UnableToRenderTemplate>) :: {

    + (view->as(`Template))
        -> map(^tpl: Template => String :: tpl->value->value)
        ->ifError(^e: Any => Error<UnableToRenderTemplate> ::
            @UnableToRenderTemplate[type: view->type, reason: e->printed])
        ?->value

};