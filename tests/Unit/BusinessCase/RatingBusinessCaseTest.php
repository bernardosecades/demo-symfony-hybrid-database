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

namespace Demo\Tests\BusinessCase;

use Demo\Entity\Rating;
use Demo\Entity\User;
use Demo\Entity\Comment;
use Demo\Entity\Vote;
use Demo\Repository\RatingRepositoryInterface;
use Demo\BusinessCase\RatingBusinessCase;
use Demo\ValueObject\VoteAction;

class RatingBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var RatingBusinessCase */
    protected $ratingBusinessCase;

    /** @var RatingRepositoryInterface */
    protected $ratingRepositoryProphesize;

    public function testSuccessSaveRating()
    {
        $rating = new Rating($this->getComment());

        $this->ratingRepositoryProphesize
            ->getRating($this->getComment())
            ->willReturn($rating);

        $this->ratingRepositoryProphesize->save($rating)->shouldBeCalled();

        $this->ratingBusinessCase->save($this->getComment(), $this->getUser(), VoteAction::UP);
    }

    /**
     * @expectedException \Demo\BusinessCase\Exception\InvalidVoteException
     * @expectedExceptionMessage User 278 already vote
     */
    public function testFailUserAlreadyVoteSaveRating()
    {
        $rating = new Rating($this->getComment());
        $rating->addVote(new Vote($this->getUser()->getId(), VoteAction::UP()));

        $this->ratingRepositoryProphesize->getRating($this->getComment())->willReturn($rating);

        $this->ratingBusinessCase->save($this->getComment(), $this->getUser(), VoteAction::UP);
    }

    /**
     * @expectedException \Demo\BusinessCase\Exception\InvalidVoteException
     * @expectedExceptionMessage You can not vote your own comment
     */
    public function testFailSameUserSaveRating()
    {
        $this->ratingBusinessCase->save($this->getComment(), $this->getComment()->getUser(), VoteAction::UP);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailBadScoreSaveRating()
    {
        $this->ratingBusinessCase->save($this->getComment(), $this->getUser(), 10);
    }

    /**
     * @return Comment
     */
    protected function getComment()
    {
        $user = new User();
        $user->setName('user1');
        $user->setId(10);

        $comment = new Comment();
        $comment->setId(156);
        $comment->setText('Lorem ipsum');
        $comment->setUser($user);

        return $comment;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        $user = new User();
        $user->setId(278);
        $user->setName('name2');

        return $user;
    }

    protected function setUp()
    {
        $this->ratingRepositoryProphesize = $this->prophesize(RatingRepositoryInterface::class);
        $this->ratingBusinessCase = new RatingBusinessCase($this->ratingRepositoryProphesize->reveal());
    }
}
