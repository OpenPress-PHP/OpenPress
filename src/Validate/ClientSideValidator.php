<?php
namespace OpenPress\Validate;

class ClientSideValidator
{
    private $schema;
    private $selector;

    public function __construct(ValidatorSchema $schema, $selector)
    {
        $this->schema = $schema;
        $this->selector = $selector;
    }

    public function render()
    {
        $fields = json_encode($this->schema->getFields());
        $data = $this->schema->toJson();
        $validators = [];
        foreach ($this->schema->getValidators() as $validator) {
            $validators[] = "'$validator': " . $this->schema->getClientValidator($validator);
        }
        $validators = implode(", ", $validators);

        $content = <<<PHP_EOL
<script>
document.addEventListener("DOMContentLoaded", function () {
    var fields = $fields;
    var data = $data;
    var form = document.querySelector('$this->selector');

    var validators = { $validators };

    if (form != null) {
        form.addEventListener("submit", function (event) {
            var formdata = {};
            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                var dfield = document.querySelector('[name=' + field + ']');
                dfield.classList.remove('is-invalid');
                dfield.classList.remove('is-valid');
                dfield.classList.add('is-valid');

                formdata[field] = dfield.value;
            }

            var errors = {};
            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                var input = document.querySelector('[name=' + field + ']');

                var checks = data[field]["validators"];
                for (var name in checks) {
                    var value = input.value;
                    var params = checks[name];

                    if (!validators[name](value, params, formdata)) {
                        if (!errors[field]) {
                            errors[field] = [];
                        }
                        errors[field].push(params && params["message"] ? params["message"] : "Input invalid");
                    }
                }
            }

            var errorsKeys = Object.keys(errors)
            if (errorsKeys.length > 0) {
                event.preventDefault();
                for (var i = 0; i < errorsKeys.length; i++) {
                    var key = errorsKeys[i];
                    var err = errors[key][0];

                    var input = document.querySelector('[name=' + key + ']');

                    var invalidFeedback = Array.from(input.parentNode.childNodes).filter(child => child.className == "invalid-feedback");

                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');

                    if (invalidFeedback.length >= 1) {
                        invalidFeedback[0].innerText = err;
                    }
                }
            }
        });
    } else {
        console.error("Form doesn't exist.");
    }
});
</script>
PHP_EOL;

        return $content;
    }
}
