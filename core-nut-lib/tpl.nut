module $tpl:

UnableToRenderTemplate := $[type: Type, reason: ?String];
UnableToRenderTemplate ==> String ::
    'Unable to render template of type ' + $type->asString + '; Reason: ' +
        ?whenIsError($reason) { 'unknown' };

Template := #Mutable<String>;

TemplateRenderer := ();
TemplateRenderer->render(^view => Result<String, UnableToRenderTemplate>) :: {
    tpl = view->as(`Template);
    ?whenTypeOf(tpl) is {
        `Template: tpl->value->value,
        ~: @UnableToRenderTemplate[type: view->type, reason: tpl->printed]
    }
};