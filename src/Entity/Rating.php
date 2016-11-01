<?php

namespace Demo\Entity;

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

use Demo\Entity\Vote;

/**
 * Class Rating
 * @package Demo\Entity
 */
class Rating
{
    /** @var Comment */
    protected $comment;

    /** @var Vote[] */
    protected $votes = [];

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return array
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param Vote $vote
     */
    public function addVote(Vote $vote)
    {
        $this->votes[$vote->getUserId()] = $vote;
    }

    /**
     * @param Vote $vote
     */
    public function delVote(Vote $vote)
    {
        if (array_key_exists($vote->getUserId(), $this->votes)) {
            unset($this->votes[$vote->getUserId()]);
        }
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}
