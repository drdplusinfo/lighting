<?php
namespace DrdPlus\Tests\Lighting;

use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Lighting\Partials\LightingQualityInterface;

class LightingQualityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $lightingQuality = new LightingQuality(-123);
        self::assertSame(-123, $lightingQuality->getValue());
        self::assertSame('-123', (string)$lightingQuality);
        self::assertInstanceOf(LightingQualityInterface::class, $lightingQuality);
    }
}