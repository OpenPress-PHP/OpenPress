<?php
namespace OpenPress\Render\Twig;

use Twig\Extension\GlobalsInterface;

class CsrfExtension extends \Twig_Extension implements GlobalsInterface
{
    /**
     * @var \Slim\Csrf\Guard
     */
    protected $csrf;

    public function __construct(\Slim\Csrf\Guard $csrf)
    {
        $this->csrf = $csrf;
    }

    public function getGlobals()
    {
        // CSRF token name and value
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();

        return [
            'csrf'   => [
                'keys' => [
                    'name'  => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name'  => $csrfName,
                'value' => $csrfValue,
                'inputs' => "<input type=\"hidden\" name=\"{$csrfNameKey}\" value=\"{$csrfName}\"><input type=\"hidden\" name=\"{$csrfValueKey}\" value=\"{$csrfValue}\">"
            ]
        ];
    }

    public function getName()
    {
        return 'slim/csrf';
    }
}
