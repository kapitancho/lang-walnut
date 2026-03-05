test $tpl:

MyViewNoTemplate := ();
MyViewWithTemplate := [v: String];
MyViewWithTemplate ==> Template :: Template(mutable{String, $v});

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Unable To Render Template Test',
            expected: `Error<UnableToRenderTemplate>,
            actual : ^ :: TemplateRenderer->render(MyViewNoTemplate)->type
        ],
        ^ => TestResult :: TestResult[
            name: 'Render Template Test',
            expected: 'hello',
            actual : ^ :: TemplateRenderer->render(MyViewWithTemplate![v: 'hello'])
        ]
    ]
};
