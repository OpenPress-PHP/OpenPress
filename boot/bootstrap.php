<?php
use OpenPress\Http\Route;
use OpenPress\Application;
use OpenPress\Locale\I18n;
use OpenPress\Content\Loader;
use Respect\Validation\Validator;
use OpenPress\Config\Configuration;
use OpenPress\Http\AssetsController;
use Symfony\Component\Finder\Finder;
use OpenPress\Validate\ValidatorSchema;
use Doctrine\Common\Annotations\AnnotationRegistry;

session_start();

require_once __DIR__ . "/../vendor/autoload.php";
define("ROOT_DIR", realpath(__DIR__ . "/.."));

foreach ((new Finder())->files()->in(ROOT_DIR . "/src/Annotation/")->name("*.php")->contains("@Annotation") as $file) {
    AnnotationRegistry::registerFile($file->getPathName());
}

$app = new Application();

/* Setup Eloquent Database */
$app->getContainer()->get("database");

/* Load enabled plugins */
$app->getContainer()->get(Loader::class)->loadPlugins();

/* Setup I18n */
I18n::setApplication($app);
I18n::setLocale(Configuration::get("locale", "en_US"));

/* Define routes */
Route::setApplication($app);
Route::register();



Route::group("/assets", function () {
    Route::get("/css", [AssetsController::class, "css"])->setName("assets.css");
    Route::get("/js", [AssetsController::class, "js"])->setName("assets.js");
});

/* Define Validators */
ValidatorSchema::addValidatorDefinition("required", function (Validator $v) {
    return $v->notEmpty();
}, "function (value, params, data) {
    return !validator.isEmpty(value);
}");

ValidatorSchema::addValidatorDefinition("email", function (Validator $v) {
    return $v->email();
}, "function (value, params, data) {
    return validator.isEmail(value);
}");

ValidatorSchema::addValidatorDefinition("equals", function (Validator $v, $params) {
    return $v->equals($params["value"]);
}, "function (value, params, data) {
    return validator.equals(value, params.value)
}");

ValidatorSchema::addValidatorDefinition("integer", function (Validator $v) {
    return $v->intVal();
}, "function (value, params, data) {
    return validator.isInt(value);
}");

ValidatorSchema::addValidatorDefinition("length", function (Validator $v, $params) {
    $min = intVal($params["min"] ?? 0);
    if ($min <= 0) {
        $min = null;
    }

    $max = intVal($params["max"] ?? 0);
    if ($max <= 0) {
        $max = null;
    }

    return $v->length($min, $max);
}, "function (value, params, data) {
    return validator.isLength(value, {min: params.min, max: params.max});
}");

ValidatorSchema::addValidatorDefinition("matches", function (Validator $v, $params, $data) {
    return $v->equals($data[$params["field"]]);
}, "function (value, params, data) {
    return validator.equals(value, data[params.field]);
}");

ValidatorSchema::addValidatorDefinition("member_of", function (Validator $v, $params) {
    return $v->in($params["values"]);
}, "function (value, params, data) {
    return validator.isIn(value, params.values);
}");

ValidatorSchema::addValidatorDefinition("range", function (Validator $v, $params) {
    if (isset($params["min"], $params["minInclusive"])) {
        $v->min(intVal($params["min"] ?? $params["minInclusive"]), isset($params["minInclusive"]));
    }

    if (isset($params["max"], $params["maxInclusive"])) {
        $v->max(intVal($params["max"] ?? $params["maxInclusive"]), isset($params["maxInclusive"]));
    }

    return $v;
}, "function (value, params, data) {
    return value > params.min && value < params.max;
}");

ValidatorSchema::addValidatorDefinition("url", function (Validator $v, $params) {
    return $v->url();
}, "function (value, params, data) {
    return validator.isURL(value);
}");

return $app;
