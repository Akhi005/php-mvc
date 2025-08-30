<?php

namespace app\mvc;

abstract class Model {
    public const RULE_REQUIRED = 'required';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';

    public array $errors = [];

    public function loadData($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    abstract public function rules(): array;

    public function validate(): bool {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute} ?? null;

            foreach ($rules as $rule) {
                $ruleName = $rule;
                $params = [];

                if (is_array($rule)) {
                    $ruleName = $rule[0];
                    $params = $rule;
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addError($attribute, self::RULE_REQUIRED);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $params['min']) {
                    $this->addError($attribute, self::RULE_MIN, $params);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $params['max']) {
                    $this->addError($attribute, self::RULE_MAX, $params);
                }
            }
        }

        return empty($this->errors);
    }

    public function addError(string $attribute, string $rule, array $params = []) {
        $message = $this->errorMessages()[$rule] ?? '';

        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        $this->errors[$attribute][] = $message;
    }

    public function errorMessages(): array {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
        ];
    }
}
