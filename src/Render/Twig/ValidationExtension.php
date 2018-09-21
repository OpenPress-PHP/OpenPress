<?php
namespace OpenPress\Render\Twig;

use Slim\Flash\Messages;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Twig\Extension\GlobalsInterface;
use OpenPress\Validate\ValidatorSchema;
use OpenPress\Validate\ClientSideValidator;
use OpenPress\Validate\ServerSideValidator;

class ValidationExtension extends \Twig_Extension implements GlobalsInterface
{
    private $errors;
    private $data;

    public function __construct(Messages $flash)
    {
        $this->errors = new MessageBag($flash->getMessage(ServerSideValidator::FLASH_KEY)[0] ?: []);
        $this->data = new Collection($flash->getMessage(ServerSideValidator::DATA_KEY)[0]);
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("is_valid", [$this, "is_valid"]),
            new \Twig_SimpleFunction("validator", [$this, "validator"])
        ];
    }

    public function getGlobals()
    {
        return [
            "validation" => [
                "errors" => $this->errors,
                "data" => $this->data
            ]
        ];
    }

    public function is_valid($name)
    {
        return $this->data->has($name) ? $this->errors->has($name) ? "is-invalid" : "is-valid" : "";
    }

    public function validator($name, $selector)
    {
        $schema = new ValidatorSchema($name);
        $validator = new ClientSideValidator($schema, $selector);

        return $validator->render();
    }
}
