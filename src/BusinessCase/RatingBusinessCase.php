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

namespace Demo\BusinessCase;

use Demo\Entity\Rating;
use Demo\Entity\User;
use Demo\Entity\Vote;
use Demo\Entity\Comment;
use Demo\ValueObject\VoteAction;
use Demo\Repository\RatingRepositoryInterface;
use Demo\BusinessCase\Exception\InvalidVoteException;

/**
 * Class RatingBusinessCase
 * @package Demo\BusinessCase
 */
class RatingBusinessCase implements RatingBusinessCaseInterface
{
    /** @var RatingRepositoryInterface */
    protected $ratingRepository;

    /**
     * @param RatingRepositoryInterface $ratingRepository
     */
    public function __construct(RatingRepositoryInterface $ratingRepository)
    {
        $this->ratingRepository = $ratingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Comment $comment, User $user, $score)
    {
        if ($comment->getUser()->getId() === $user->getId()) {
            throw new InvalidVoteException('You can not vote your own comment');
        }

        $voteAction = new VoteAction($score);
        $rating = $this->ratingRepository->getRating($comment);

        if ($rating) {
            $votes = $rating->getVotes();
            if (array_key_exists($user->getId(), $votes)) {
                throw new InvalidVoteException(sprintf('User %d already vote', $user->getId()));
            }
        } else {
            $rating = new Rating($comment);
        }

        $rating->addVote(new Vote($user->getId(), $voteAction));
        $this->ratingRepository->save($rating);

        return $rating;
    }
}
