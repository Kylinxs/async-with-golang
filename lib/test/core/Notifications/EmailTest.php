
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Tests\Notifications;

use Comments;
use PHPUnit\Framework\TestCase;
use Tiki\Notifications\Email;
use TikiLib;

class EmailTest extends TestCase
{
    protected static $objects = [];

    protected function setUp(): void
    {
        $_SERVER["SERVER_NAME"] = 'test.example.org';
    }

    public static function tearDownAfterClass(): void
    {
        $commentslib = TikiLib::lib('comments');
        foreach (self::$objects['comments'] as $commentId) {
            $commentslib->remove_comment($commentId);
        }

        $commentslib = TikiLib::lib('comments');
        foreach (self::$objects['forums'] as $forumId) {
            $commentslib->remove_forum($forumId);
        }

        // Remove forum, removes all forum posts/comments
        $bloglib = TikiLib::lib('blog');
        foreach (self::$objects['blogs'] as $blogId) {
            $bloglib->remove_blog($blogId);
        }
    }

    /**
     * @covers \Tiki\Notifications\Email::getEmailThreadHeaders()
     */
    public function testGetEmailThreadHeadersForForums()
    {
        global $user;

        // make sure the threads pref is set
        $tikilib = TikiLib::lib('tiki');
        $tikilib->set_preference('forum_notifications_use_new_threads', 'n');

        /** @var Comments $commentsLib */
        $commentsLib = TikiLib::lib('comments');
        $forumId = $commentsLib->replace_forum(0, 'Test Forum');

        self::$objects['forums'][] = $forumId;

        $messageId = '';
        $rand = random_int(0, 9);

        //In a forumthread there is a first post (no parent available):
        $commentId = $commentsLib->post_new_comment(
            'forum:' . $forumId,
            0,
            $user,
            'Test forum thread comment - ' . $rand,
            'This is a test - ' . $rand,
            $messageId
        );

        self::$objects['comments'][] = $commentId;

        $md5Header = md5('forum.' . $forumId) . '@' . $_SERVER["SERVER_NAME"];
        $headers = Email::getEmailThreadHeaders('forum', $commentId);

        $this->assertEquals($messageId, $headers['Message-Id']);
        $this->assertArrayNotHasKey('In-Reply-To', $headers);
        $this->assertEquals($md5Header, $headers['References'] ?? null);

        //2nd post in same thread:

        $messageId2 = '';
        $rand2 = random_int(10, 19);

        $commentId2 = $commentsLib->post_new_comment(
            'forum:' . $forumId,
            $commentId,
            $user,
            'Test forum thread comment - ' . $rand2,
            'This is a test - ' . $rand2,
            $messageId2,
            $messageId
        );

        self::$objects['comments'][] = $commentId2;

        $headers2 = Email::getEmailThreadHeaders('forum', $commentId2);

        $this->assertEquals($messageId2, $headers2['Message-Id']);
        $this->assertEquals($messageId, $headers2['In-Reply-To']);
        $this->assertEquals($md5Header . ' ' . $messageId, $headers2['References']);

        //3rd post in same thread (no reply):
        $messageId3 = '';
        $rand3 = random_int(20, 29);

        $commentId3 = $commentsLib->post_new_comment(
            'forum:' . $forumId,
            0,
            $user,
            'Test forum thread comment - ' . $rand3,
            'This is a test - ' . $rand3,
            $messageId3
        );

        self::$objects['comments'][] = $commentId3;

        $headers3 = Email::getEmailThreadHeaders('forum', $commentId3);

        $this->assertEquals($messageId3, $headers3['Message-Id']);
        $this->assertArrayNotHasKey('In-Reply-To', $headers3);
        $this->assertEquals($md5Header, $headers3['References']);

        //4th post in same thread, reply to comment 2:
        $messageId4 = '';
        $rand4 = random_int(30, 39);

        $commentId4 = $commentsLib->post_new_comment(
            'forum:' . $forumId,
            $commentId2,
            $user,
            'Test forum thread comment - ' . $rand4,
            'This is a test - ' . $rand4,
            $messageId4,
            $messageId2
        );

        self::$objects['comments'][] = $commentId4;

        $headers4 = Email::getEmailThreadHeaders('forum', $commentId4);

        $this->assertEquals($messageId4, $headers4['Message-Id']);
        $this->assertEquals($messageId2, $headers4['In-Reply-To']);
        $this->assertEquals($md5Header . ' ' . $messageId . ' ' . $messageId2, $headers4['References']);
    }

    /**
     * @covers \Tiki\Notifications\Email::getEmailThreadHeaders()
     */
    public function testGetEmailThreadHeadersForBlogPosts()
    {
        global $user;

        // make sure the threads pref is set
        $tikilib = TikiLib::lib('tiki');
        $tikilib->set_preference('forum_notifications_use_new_threads', 'n');
        $tikilib->set_preference('user_blog_watch_editor', 'n');

        $commentsLib = TikiLib::lib('comments');
        $blogLib = TikiLib::lib('blog');
        $blogId = $blogLib->replace_blog('Test Blog', '', 'admin', 'y', 25, 0, '', 'y', 'y', 'y', 'y', 'y', 'y', 'n', 'y', 'n', 'n', '', 'n', 5, 'n');

        self::$objects['blogs'][] = $blogId;

        $blogPostId = $blogLib->blog_post($blogId, 'Test blog post', 'Test blog post', $user, 'Test blog post');
        self::$objects['blog_posts'][] = $blogPostId;


        $messageId = '';
        $rand = random_int(0, 9);

        //1st comment:
        $commentId = $commentsLib->post_new_comment(
            'blog post:' . $blogPostId,
            0,
            $user,
            'Test blog post comment - ' . $rand,
            'This is a test - ' . $rand,
            $messageId
        );

        self::$objects['comments'][] = $commentId;

        $md5Header = md5('blog post.' . $blogPostId) . '@' . $_SERVER["SERVER_NAME"];
        $headers = Email::getEmailThreadHeaders('blog post', $commentId);

        $this->assertEquals($messageId, $headers['Message-Id']);
        $this->assertArrayNotHasKey('In-Reply-To', $headers);
        $this->assertEquals($md5Header, $headers['References'] ?? null);

        //2nd comment (reply to first - will have a parentId but no in_reply_to in database)
        $messageId2 = '';
        $rand2 = random_int(10, 19);

        $commentId2 = $commentsLib->post_new_comment(
            'blog post:' . $blogPostId,
            $commentId,
            $user,
            'Test blog post comment - ' . $rand2,
            'This is a test - ' . $rand2,
            $messageId2
        );

        self::$objects['comments'][] = $commentId2;

        $headers2 = Email::getEmailThreadHeaders('blog post', $commentId2);

        $this->assertEquals($messageId2, $headers2['Message-Id']);
        $this->assertEquals($messageId, $headers2['In-Reply-To']);
        $this->assertEquals($md5Header . ' ' . $messageId, $headers2['References']);

        //3rd comment (no reply to other comment):
        $messageId3 = '';
        $rand3 = random_int(20, 29);

        $commentId3 = $commentsLib->post_new_comment(
            'blog post:' . $blogPostId,
            0,
            $user,
            'Test blog post comment - ' . $rand3,
            'This is a test - ' . $rand3,
            $messageId3
        );

        self::$objects['comments'][] = $commentId3;

        $headers3 = Email::getEmailThreadHeaders('blog post', $commentId3);

        $this->assertEquals($messageId3, $headers3['Message-Id']);
        $this->assertArrayNotHasKey('In-Reply-To', $headers3);
        $this->assertEquals($md5Header, $headers3['References']);

        //4th comment, reply to comment 2:
        $messageId4 = '';
        $rand4 = random_int(30, 39);

        $commentId4 = $commentsLib->post_new_comment(
            'blog post:' . $blogPostId,
            $commentId2,
            $user,
            'Test blog post comment - ' . $rand4,
            'This is a test - ' . $rand4,
            $messageId4
        );

        self::$objects['comments'][] = $commentId4;

        $headers4 = Email::getEmailThreadHeaders('blog post', $commentId4);

        $this->assertEquals($messageId4, $headers4['Message-Id']);
        $this->assertEquals($messageId2, $headers4['In-Reply-To']);
        $this->assertEquals($md5Header . ' ' . $messageId . ' ' . $messageId2, $headers4['References']);
    }
}