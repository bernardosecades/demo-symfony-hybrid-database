<?php

/**
 * MIT License
 *
 * Copyright (c) 2016 Bernardo Secades
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Demo\ValueObject;

/**
 * Class ErrorResponse
 * @package Demo\ErrorResponse
 */
class ErrorResponse
{
    /** @var array  */
    protected $errors = [];

    /** @var int */
    protected $errorCode;

    /** @var int */
    protected $totalErrors;

    /**
     * @param array     $errors
     * @param ErrorCode $errorCode
     */
    public function __construct(array $errors, ErrorCode $errorCode)
    {
        $this->errors      = $errors;
        $this->errorCode   = $errorCode->getValue();
        $this->totalErrors = count($errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getTotalErrors()
    {
        return $this->totalErrors;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
