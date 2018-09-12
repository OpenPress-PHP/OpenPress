<?php
namespace OpenPress\Validate;

use Slim\Http\Request;
use Slim\Flash\Messages as Flash;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    const FLASH_KEY = "validation.errors";
    const DATA_KEY  = "validation.data";

    private $request;
    private $flash;
    private $errors = [];
    private $data = [];

    public function __construct(Request $request, Flash $flash)
    {
        $this->request = $request;
        $this->flash = $flash;
    }

    public function check(array $rules)
    {
        foreach ($rules as $name => $ruleSet) {
            $split = explode('|', $name);
            try {
                $value = $this->request->getParsedBodyParam($split[0]);
                if ($ruleSet !== null) {
                    $ruleSet->setName(isset($split[1]) ? $split[1] : ucfirst($split[0]))
                        ->assert($value);
                }
                $this->data[$split[0]] = $value;
            } catch (NestedValidationException $e) {
                $this->errors[$split[0]] = $e->getMessages();
            }
        }
        $this->flash->addMessage(static::DATA_KEY, $this->data);

        if ($this->failed()) {
            $this->flash->addMessage(static::FLASH_KEY, $this->errors);
        }
    }

    public function passed()
    {
        return count($this->errors) == 0;
    }

    public function failed()
    {
        return !$this->passed();
    }

    public function messages()
    {
        return $this->errors;
    }

    public function data()
    {
        return $this->data;
    }
}
