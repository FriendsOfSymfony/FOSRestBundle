<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use Composer\InstalledVersions;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Tests\Fixtures\Controller\ParamsAnnotatedController;
use PHPUnit\Framework\TestCase;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
class ParamReaderTest extends TestCase
{
    private $paramReader;

    private static $validatorSupportsAnnotations = true;

    public static function setUpBeforeClass(): void
    {
        $validatorSupportsAnnotations = true;

        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('symfony/validator')) {
            $validatorVersion = InstalledVersions::getVersion('symfony/validator');

            $validatorSupportsAnnotations = null !== $validatorVersion && version_compare($validatorVersion, '7.0', '<');
        }

        self::$validatorSupportsAnnotations = $validatorSupportsAnnotations;
    }

    protected function setUp(): void
    {
        // An annotation reader is only injected when `doctrine/annotations` is installed and `symfony/validator` is installed at a version supporting annotations
        if (interface_exists(Reader::class) && self::$validatorSupportsAnnotations) {
            $this->paramReader = new ParamReader(new AnnotationReader());
        } else {
            $this->paramReader = new ParamReader();
        }
    }

    public function testReadsAnnotations()
    {
        if (!interface_exists(Reader::class)) {
            $this->markTestSkipped('Test requires doctrine/annotations');
        }

        if (!self::$validatorSupportsAnnotations) {
            $this->markTestSkipped('Test requires symfony/validator:<7.0');
        }

        $params = $this->paramReader->read(new \ReflectionClass(ParamsAnnotatedController::class), 'getArticlesAction');

        $this->assertCount(6, $params);

        foreach ($params as $name => $param) {
            $this->assertInstanceOf(ParamInterface::class, $param);
            $this->assertEquals($param->getName(), $name);
        }

        // Param 1 (query)
        $this->assertArrayHasKey('page', $params);
        $this->assertEquals('page', $params['page']->name);
        $this->assertEquals('\\d+', $params['page']->requirements);
        $this->assertEquals('1', $params['page']->default);
        $this->assertEquals('Page of the overview.', $params['page']->description);
        $this->assertFalse($params['page']->map);
        $this->assertFalse($params['page']->strict);

        // Param 2 (request)
        $this->assertArrayHasKey('byauthor', $params);
        $this->assertEquals('byauthor', $params['byauthor']->name);
        $this->assertEquals('[a-z]+', $params['byauthor']->requirements);
        $this->assertEquals('by author', $params['byauthor']->description);
        $this->assertEquals(['search'], $params['byauthor']->incompatibles);
        $this->assertFalse($params['byauthor']->map);
        $this->assertTrue($params['byauthor']->strict);

        // Param 3 (query)
        $this->assertArrayHasKey('filters', $params);
        $this->assertEquals('filters', $params['filters']->name);
        $this->assertTrue($params['filters']->map);

        // Param 4 (file)
        $this->assertArrayHasKey('avatar', $params);
        $this->assertEquals('avatar', $params['avatar']->name);
        $this->assertEquals(['mimeTypes' => 'application/json'], $params['avatar']->requirements);
        $this->assertTrue($params['avatar']->image);
        $this->assertTrue($params['avatar']->strict);

        // Param 5 (file)
        $this->assertArrayHasKey('foo', $params);
        $this->assertEquals('foo', $params['foo']->name);
        $this->assertFalse($params['foo']->image);
        $this->assertFalse($params['foo']->strict);
    }

    /**
     * @requires PHP 8
     */
    public function testReadsAttributes()
    {
        $params = $this->paramReader->read(new \ReflectionClass(ParamsAnnotatedController::class), 'getArticlesAttributesAction');

        $this->assertCount(6, $params);

        foreach ($params as $name => $param) {
            $this->assertInstanceOf(ParamInterface::class, $param);
            $this->assertEquals($param->getName(), $name);
        }

        // Param 1 (query)
        $this->assertArrayHasKey('page', $params);
        $this->assertEquals('page', $params['page']->name);
        $this->assertEquals('\\d+', $params['page']->requirements);
        $this->assertEquals('1', $params['page']->default);
        $this->assertEquals('Page of the overview.', $params['page']->description);
        $this->assertFalse($params['page']->map);
        $this->assertFalse($params['page']->strict);

        // Param 2 (request)
        $this->assertArrayHasKey('byauthor', $params);
        $this->assertEquals('byauthor', $params['byauthor']->name);
        $this->assertEquals('[a-z]+', $params['byauthor']->requirements);
        $this->assertEquals('by author', $params['byauthor']->description);
        $this->assertEquals(['search'], $params['byauthor']->incompatibles);
        $this->assertFalse($params['byauthor']->map);
        $this->assertTrue($params['byauthor']->strict);

        // Param 3 (query)
        $this->assertArrayHasKey('filters', $params);
        $this->assertEquals('filters', $params['filters']->name);
        $this->assertTrue($params['filters']->map);

        // Param 4 (file)
        $this->assertArrayHasKey('avatar', $params);
        $this->assertEquals('avatar', $params['avatar']->name);
        $this->assertEquals(['mimeTypes' => 'application/json'], $params['avatar']->requirements);
        $this->assertTrue($params['avatar']->image);
        $this->assertTrue($params['avatar']->strict);

        // Param 5 (file)
        $this->assertArrayHasKey('foo', $params);
        $this->assertEquals('foo', $params['foo']->name);
        $this->assertFalse($params['foo']->image);
        $this->assertFalse($params['foo']->strict);
    }

    public function testExceptionOnNonExistingMethod()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Class "%s" has no method "foo".', self::class));

        $this->paramReader->read(new \ReflectionClass(__CLASS__), 'foo');
    }
}
