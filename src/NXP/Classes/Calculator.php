<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP\Classes;

use NXP\Classes\Token\InterfaceOperator;
use NXP\Classes\Token\TokenFunction;
use NXP\Classes\Token\TokenNumber;
use NXP\Classes\Token\TokenVariable;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\UnknownVariableException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class Calculator
{
    /**
     * Calculate array of tokens in reverse polish notation
     * @param  array                                       $tokens    Array of tokens
     * @param  array                                       $variables Array of variables
     * @param  array                                       $midresults Array of middle results
     * @param  \NXP\MathExecutor                           $oExecutor object of NXP\MathExecutor
     * @return number                                      Result
     * @throws \NXP\Exception\IncorrectExpressionException
     * @throws \NXP\Exception\UnknownVariableException
     */
    public function calculate($tokens, $variables, $midresults, $oExecutor)
    {
        $stack = array();
        foreach ($tokens as $token) {
            if ($token instanceof TokenNumber) {
                array_push($stack, $token);
            }
            if ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if(array_key_exists($variable, $variables)) {
                    $value = $variables[$variable];
                } elseif (array_key_exists($variable, $midresults)) { //recursive calculation of intermediate result
                    if(isset($midresults[$variable]['value'])) { //if intermediate has 'value' directly use it
                        $value = $midresults[$variable]['value'];
                    } else {
                        $value = $oExecutor->execute($midresults[$variable]['formula']);
                    }
                } else {
                    throw new UnknownVariableException();
                }

                /*
                if (!array_key_exists($variable, $variables)) {
                    throw new UnknownVariableException();
                }
                $value = $variables[$variable];
                */

                array_push($stack, new TokenNumber($value));
            }
            if ($token instanceof InterfaceOperator || $token instanceof TokenFunction) {
                array_push($stack, $token->execute($stack));
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new IncorrectExpressionException();
        }

        return $result->getValue();
    }
}
