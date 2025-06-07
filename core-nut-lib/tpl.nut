module $tpl:

UnableToRenderTemplate = $[type: Type];
UnableToRenderTemplate ==> String :: 'Unable to render template of type '->concat($type->asString);

Template = #Mutable<String>;

TemplateRenderer = :[];
TemplateRenderer->render(^view: Any => Result<String, UnableToRenderTemplate>) :: {
    tpl = view->as(`Template);
    ?whenTypeOf(tpl) is {
        `Template: tpl->value->value,
        ~: @UnableToRenderTemplate[view->type]
    }
};

