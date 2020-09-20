<?php

namespace DpkgBrowser\Classes;

use InvalidArgumentException;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * Class Args
 *
 * Extends Respect\Validation so that it provides function/method argument
 * validation.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes
 */
class Args extends Validator {

    /**
     * Validates an argument with the given ruleset.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param  mixed $value The value of the argument to validate.
     * @param \Respect\Validation\Validatable $rule The ruleset to validate the
     *  value with.
     *
     * @throws \LogicException Thrown if:
     *  - the argument being validated does not exist.
     *  - the argument validation rule does not exist.
     *  - the validation rule does not extend from
     *    \Respect\Validation\Validatable
     * @throws \InvalidArgumentException If the argument is not valid then the
     *  exception is thrown.
     * @throws \ReflectionException
     */
    public static function v($value, Validatable $rule) {
        // if we are in production we want a speed up to prevent excess
        // validation on data types.
        if(ENVIRONMENT === 'production') {
            return;
        }

        $rule_instance = Validatable::class;

        $args = func_get_args();
        for ($i = 0, $l = count($args); $i < $l; $i += 2) {

            $arg_index = $i / 2;

            if (array_key_exists($i, $args) === false) {
                throw new LogicException('Args::v exception: The argument value expected at index ' . $i . ' does not exist.');
            }
            if (array_key_exists($i + 1, $args) === false) {
                throw new LogicException('Args::v exception: The argument validation rule expected at index ' . ($i + 1) . ' does not exist.');
            }

            $arg_value = $args[$i];
            $arg_rule = $args[$i + 1];
            if ($arg_rule instanceof $rule_instance === false) {
                throw new LogicException('Args::v exception: The rule at index ' . ($i + 1) . ' is not an interface of "Respect\Validation\Validatable"');
            }

            try {
                $arg_rule->assert($arg_value);
            } catch (InvalidArgumentException $e) {

                $trace = debug_backtrace();
                $caller = $trace[1];

                if (isset($caller['class']) === true) {
                    $function_name = $caller['class'] . $caller['type'] . $caller['function'];
                    $reflection = new ReflectionMethod($caller['class'], $caller['function']);
                } else {
                    $function_name = $caller['function'];
                    $reflection = new ReflectionFunction($caller['function']);
                }

                $params = $reflection->getParameters();
                $param_name = lcfirst($params[$arg_index]);

                if (method_exists($e, 'setName') === true) {
                    $e->setName($param_name);
                }

                if (method_exists($e, 'getFullMessage') === true) {
                    $message = $e->getFullMessage();
                } else {
                    $message = $e->getMessage();
                }

                $lines = explode(PHP_EOL, $message);
                array_walk(
                    $lines, function (&$item) {
                        $item = '   ' . $item;
                    }
                );
                $message = implode(PHP_EOL, $lines);

                throw new InvalidArgumentException($function_name . ' ' . $param_name . ' is not valid. ' . print_r($arg_value, true) . ' (' . gettype($arg_value) . ') given. Failed with: ' . PHP_EOL . $message, 0);
            }
        }
    }

}