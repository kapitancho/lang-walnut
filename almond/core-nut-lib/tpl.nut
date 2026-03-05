module $tpl:

UnableToRenderTemplate := $[type: Type, reason: ?String];
UnableToRenderTemplate ==> String ::
    'Unable to render template of type ' + $type->asString + '; Reason: ' +
        ?whenIsError($reason) { 'unknown' };

Template := #Mutable<String>;

TemplateRenderer := ();
TemplateRenderer->render(^view => Result<String, UnableToRenderTemplate>) :: {
    tpl = view->as(`Template);
    ?whenIsError(tpl) {
        @UnableToRenderTemplate[type: view->type, reason: tpl->error->printed]
    } ~ {
        tpl->value->value
    };
};