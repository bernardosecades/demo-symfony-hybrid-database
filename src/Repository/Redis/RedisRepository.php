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

namespace Demo\Repository\Redis;

use Redis;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RedisRepository
 * @package Demo\Repository\Redis
 */
class RedisRepository
{
    /** @var Redis  */
    private $client;

    /** @var array */
    protected $options;

    /**
     * @param Redis $client
     * @param array $options
     */
    public function __construct(Redis $client, array $options = [])
    {
        $this->client = $client;
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'host'     => '127.0.0.1',
            'port'     => 6379,
        ));

        $this->options = $resolver->resolve($options);
    }

    /**
     * @return Redis
     */
    protected function getClient()
    {
        $this->client->pconnect($this->options['host'], $this->options['port']);

        return $this->client;
    }

    /**
     * @param string $key
     * @param array  $values
     * @param string $command Example: sAdd, sRemove, lPush, rPush ...
     */
    protected function executeCommandMultiValue($key, array $values, $command)
    {
        array_unshift($values, $key);
        call_user_func_array([$this->getClient(), $command], $values);
    }
}
