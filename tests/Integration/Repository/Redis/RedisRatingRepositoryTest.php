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

namespace Demo\Tests\Integration\Repository\Redis;

use Demo\Entity\User;
use Demo\Entity\Vote;
use Demo\Entity\Rating;
use Demo\Entity\Comment;
use Demo\ValueObject\VoteAction;
use Demo\Tests\Integration\IntegrationTestCase;
use Demo\Repository\Redis\RedisRatingRepository;
use Redis;

class RedisRatingRepositoryTest extends IntegrationTestCase
{
    /** @var  Redis */
    protected $redis;

    /** @var  RedisRatingRepository */
    protected $redisRatingRepository;

    public function noInitializedInRedis()
    {
        $comment = $this->getComment();
        $rating = $this->redisRatingRepository->getRating($comment);
        $this->assertNull($rating);
    }

    public function testSaveNewRatingSingleUser()
    {
        $comment = $this->getComment();

        $rating = new Rating($comment);
        $rating->addVote(new Vote(100, VoteAction::UP()));
        $rating->addVote(new Vote(101, VoteAction::UP()));
        $rating->addVote(new Vote(102, VoteAction::UP()));
        $rating->addVote(new Vote(103, VoteAction::DOWN()));

        $this->redisRatingRepository->save($rating);

        $key = sprintf('rating:%s', $comment->getId());

        $this->assertTrue($this->redis->exists($key));
        $this->assertEquals(4, $this->redis->sCard($key));
        $this->assertTrue($this->redis->sIsMember($key, '100:1'));
        $this->assertTrue($this->redis->sIsMember($key, '101:1'));
        $this->assertTrue($this->redis->sIsMember($key, '102:1'));
        $this->assertTrue($this->redis->sIsMember($key, '103:-1'));

        return $rating;
    }

    public function testMultipleUpdateRating()
    {
        $comment = $this->getComment();
        $key = sprintf('rating:%s', $comment->getId());

        // Add one vote from user 100
        $voteA = new Vote(100, VoteAction::UP());
        $rating = new Rating($comment);
        $rating->addVote($voteA);
        $this->redisRatingRepository->save($rating);

        $this->assertTrue($this->redis->exists($key));
        $this->assertEquals(1, $this->redis->sCard($key));
        $this->assertTrue($this->redis->sIsMember($key, '100:1'));

        // Add other vote from user 101
        $voteB = new Vote(101, VoteAction::UP());
        $rating->addVote($voteB);
        $this->redisRatingRepository->save($rating);

        $this->assertTrue($this->redis->exists($key));
        $this->assertEquals(2, $this->redis->sCard($key));
        $this->assertTrue($this->redis->sIsMember($key, '100:1'));
        $this->assertTrue($this->redis->sIsMember($key, '101:1'));

        // Dell vote from user 100
        $rating->delVote($voteA);
        $this->redisRatingRepository->save($rating);

        $this->assertTrue($this->redis->exists($key));
        $this->assertEquals(1, $this->redis->sCard($key));
        $this->assertFalse($this->redis->sIsMember($key, '100:1'));
        $this->assertTrue($this->redis->sIsMember($key, '101:1'));

        // Del vote from user 101
        $rating->delVote($voteB);
        $this->redisRatingRepository->save($rating);

        $this->assertFalse($this->redis->exists($key));

        $this->assertEquals(0,  $this->redisRatingRepository->getTotalRating($comment));
    }

    /**
     * @depends testSaveNewRatingSingleUser
     */
    public function testTotalScore()
    {
        /** @var Rating $rating */
        $rating = func_get_args()[0];
        $this->redisRatingRepository->save($rating);

        $this->assertEquals(2,  $this->redisRatingRepository->getTotalRating($this->getComment()));
    }

    /**
     * @depends testSaveNewRatingSingleUser
     */
    public function testTotalScoreNegative()
    {
        /** @var Rating $rating */
        $rating = func_get_args()[0];
        $rating->addVote(new Vote(104, VoteAction::DOWN()));
        $rating->addVote(new Vote(105, VoteAction::DOWN()));
        $rating->addVote(new Vote(106, VoteAction::DOWN()));
        $this->redisRatingRepository->save($rating);

        $this->assertEquals(-1, $this->redisRatingRepository->getTotalRating($this->getComment()));
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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var Redis $redis */
        $this->redis = $this->get('demo.redis');
        // Parameters from test environment -> parameters_test.yml
        $options = $this->getContainer()->getParameter('redis');
        $this->redisRatingRepository = new RedisRatingRepository($this->redis, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->redis->flushDB();
    }
}
