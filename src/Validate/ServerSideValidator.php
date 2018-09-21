<?php
namespace OpenPress\Validate;

use Slim\Http\Request;
use Respect\Validation\Validator;
use Slim\Flash\Messages as Flash;
use Respect\Validation\Exceptions\NestedValidationException;

class ServerSideValidator
{
    const FLASH_KEY = "validation.errors";
    const DATA_KEY  = "validation.data";

    private $schema;
    private $errors = [];

    public function __construct(ValidatorSchema $schema)
    {
        $this->schema = $schema;
    }

    public function validate(Request $request, Flash $flash)
    {
        foreach ($this->schema->getFields() as $field) {
            $definition = $this->schema->get($field);
            foreach ($definition["validators"] as $name => $params) {
                try {
                    $this->schema->parseForServer(new Validator, $name, $params, $request->getParams())
                        ->setName($definition["filters"]["name"] ?? $field)
                        ->assert($request->getParam($field));
                } catch (NestedValidationException $e) {
                    $this->errors[$field][$name] = $params["message"] ?? $e->getMessages()[0];
                }
            }
        }

        if ($this->failed()) {
            $flash->addMessage(ServerSideValidator::FLASH_KEY, $this->errors);
            $flash->addMessage(ServerSideValidator::DATA_KEY, $request->getParams());
        }
    }

    public function passed()
    {
        return count($this->errors) === 0;
    }

    public function failed()
    {
        return !$this->passed();
    }
}
