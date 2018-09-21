<?php
use Respect\Validation\Validator;
use OpenPress\Validate\ValidatorSchema;

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
