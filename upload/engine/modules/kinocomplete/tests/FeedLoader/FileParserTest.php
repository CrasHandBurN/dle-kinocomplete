<?php

namespace Kinocomplete\Test\FeedLoader;

use JsonMachine\Exception\PathNotFoundException;
use Kinocomplete\FeedLoader\FileParser;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Video\Video;
use Kinocomplete\Feed\Feeds;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feed;

class FileParserTest extends TestCase
{
  /**
   * Testing `parseFeed` method.
   *
   * @throws \Exception
   */
  public function testCanParse()
  {
    $instance = new FileParser(
      FIXTURES_DIR .'/moonwalk-feed'
    );

    $feed = Feeds::get(
      'foreign-movies',
      Video::MOONWALK_ORIGIN
    );

    $bytesParsed = 0;
    $itemsParsed = 0;

    $onProgress = function (
      $totalBytes,
      $parsedBytes
    ) use (&$bytesParsed) {
      $bytesParsed = $parsedBytes;
    };

    $onParse = function () use (&$itemsParsed) {
      ++$itemsParsed;
    };

    $instance->parse(
      $feed,
      $onParse,
      $onProgress
    );

    Assert::greaterThan($bytesParsed, 0);
    Assert::same($itemsParsed, 2);
  }

  /**
   * Testing `parseFeed` method by broken file.
   *
   * @throws \Exception
   */
  public function testCanParseBrokenFile()
  {
    $instance = new FileParser(
      FIXTURES_DIR .'/moonwalk-feed'
    );

    $feed = new Feed();
    $feed->setName('broken');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(49719268);

    $bytesParsed = 0;
    $itemsParsed = 0;

    $onProgress = function (
      $totalBytes,
      $parsedBytes
    ) use (&$bytesParsed) {
      $bytesParsed = $parsedBytes;
    };

    $onParse = function () use (&$itemsParsed) {
      ++$itemsParsed;
    };

    $instance->parse(
      $feed,
      $onParse,
      $onProgress,
      true
    );

    Assert::same($bytesParsed, 0);
    Assert::same($itemsParsed, 0);
  }

  /**
   * Testing `parseFeed` method exceptions.
   *
   * @throws \Exception
   */
  public function testCannotParseBrokenFile()
  {
    $this->expectException(PathNotFoundException::class);

    $instance = new FileParser(
      FIXTURES_DIR .'/moonwalk-feed'
    );

    $feed = new Feed();
    $feed->setName('broken');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(49719268);

    $instance->parse($feed);
  }
}
