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

namespace Demo\Repository\Doctrine\ORM;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\ORM\EntityRepository;

/**
 * Class DoctrineRepository
 * @package Demo\Repository\Doctrine\ORM
 */
class DoctrineRepository extends EntityRepository
{
    /**
     * @param mixed $entity
     */
    public function refresh($entity)
    {
        $this->getEntityManager()->refresh($entity);
    }

    /**
     * Executes an, optionally parametrized, SQL query.
     *
     * If the query is parametrized, a prepared statement is used.
     * If an SQLLogger is configured, the execution is logged.
     *
     * @param string                                      $query  The SQL query to execute.
     * @param array                                       $params The parameters to bind to the query, if any.
     * @param array                                       $types  The types the previous parameters are in.
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp    The query cache profile, optional.
     *
     * @return \Doctrine\DBAL\Driver\Statement The executed statement.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * @param mixed $entity
     */
    protected function saveEntity($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);
    }
}
