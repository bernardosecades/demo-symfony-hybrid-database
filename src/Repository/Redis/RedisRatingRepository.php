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

use Demo\Entity\Vote;
use Demo\Entity\Rating;
use Demo\Entity\Comment;
use Demo\ValueObject\VoteAction;
use Demo\Repository\RatingRepositoryInterface;

/**
 * Class RedisRatingRepository
 * @package Demo\Repository\Redis
 */
class RedisRatingRepository extends RedisRepository implements RatingRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function save(Rating $rating)
    {
        $key = $this->getKey($rating->getComment());
        $ratingInRedis = $this->getRating($rating->getComment());

        if (null === $ratingInRedis) {
            $this->executeCommandMultiValue($key, $this->getValues($rating->getVotes()), 'sAdd');
        } else {
            $votesRedis = $ratingInRedis->getVotes();
            $votes = $rating->getVotes();
            $userIdsRedis = array_keys($votesRedis);
            $userIds = array_keys($votes);
            $userIdsToAdd = array_diff($userIds, $userIdsRedis);
            $userIdsToDel = array_diff($userIdsRedis, $userIds);
            if (!empty($userIdsToAdd)) {
                $this->executeCommandMultiValue($key, $this->getValues($votes, $userIdsToAdd), 'sAdd');
            }
            if (!empty($userIdsToDel)) {
                $values = $this->getValues($votesRedis, $userIdsToDel);
                $this->executeCommandMultiValue($key, $values, 'sRemove');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRating(Comment $comment)
    {
        if (!$this->getClient()->exists($this->getKey($comment))) {
            return null;
        }

        $rating = new Rating($comment);
        $values = $this->getClient()->sMembers($this->getKey($comment));

        foreach ($values as $value) {
            list($userId, $score) = $this->getSplitValues($value);
            $vote = new Vote($userId, new VoteAction($score));
            $rating->addVote($vote);
        }

        return $rating;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRating(Comment $comment)
    {
        $rating = $this->getRating($comment);

        if (null === $rating) {
            return 0;
        }

        $total = 0;
        /** @var Vote $vote */
        foreach ($rating->getVotes() as $vote) {
            $total += $vote->getScore();
        }

        return $total;
    }

    /**
     * @param Comment $comment
     * @return string
     */
    protected function getKey(Comment $comment)
    {
        return sprintf('rating:%s', $comment->getId());
    }

    /**
     * @param string $value Format $value -> userId:score, Example: 7612:1, 1000:-1
     * @return array
     */
    protected function getSplitValues($value)
    {
        list($userId, $score) = explode(':', $value);

        return [
            intval($userId),
            intval($score),
        ];
    }

    /**
     * @param array $votes
     * @param array $userIdFilter
     * @return array
     */
    protected function getValues(array $votes, array $userIdFilter = [])
    {
        $values = [];
        /**
         * @var  int  $userId
         * @var  Vote $vote
         */
        foreach ($votes as $userId => $vote) {
            if (!empty($userIdFilter) && !in_array($userId, $userIdFilter)) {
                continue;
            }
            $values[] = sprintf('%s:%s', $vote->getUserId(), $vote->getScore());
        }

        return $values;
    }
}
