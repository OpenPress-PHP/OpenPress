<?php
namespace OpenPress\Render\Twig;

use OpenPress\Locale\I18n;

class I18nExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("__", [$this, "localize"]),
            new \Twig_SimpleFunction("localize", [$this, "localize"])
        ];
    }

    public function localize($key)
    {
        return I18n::get($key);
    }
}
