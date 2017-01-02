<?php
namespace DrdPlus\Tests\Lighting;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Lighting\Contrast;
use DrdPlus\Lighting\EyeAdaptation;
use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Tables\Races\SightRangesTable;
use Granam\Tests\Tools\TestWithMockery;

class ContrastTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider providePreviousAndCurrentLightIntensity
     * @param int $previousLightIntensityValue
     * @param int $currentLightIntensityValue
     * @param int $expectedContrast
     */
    public function I_can_create_it_by_simplified_rules($previousLightIntensityValue, $currentLightIntensityValue, $expectedContrast)
    {
        $contrast = Contrast::createBySimplifiedRules(
            new LightingQuality($previousLightIntensityValue),
            new LightingQuality($currentLightIntensityValue)
        );
        self::assertSame($expectedContrast, $contrast->getValue());
        $asString = (string)$expectedContrast;
        if ($contrast->isFromLightToDark()) {
            $asString .= ' (to dark)';
        }
        if ($contrast->isFromDarkToLight()) {
            $asString .= ' (to light)';
        }
        self::assertSame($asString, (string)$contrast);
        self::assertSame($previousLightIntensityValue > $currentLightIntensityValue, $contrast->isFromLightToDark());
        self::assertSame($previousLightIntensityValue < $currentLightIntensityValue, $contrast->isFromDarkToLight());
    }

    public function providePreviousAndCurrentLightIntensity()
    {
        return [
            [-123, 345, 47], // negative, rounded up (removed decimals)
            [678, 1, 67], // positive, rounded down (removed decimals)
            [-5, -5, 0], // same
        ];
    }

    /**
     * @test
     * @dataProvider provideAdaptationCurrentLightIntensityAndAdaptability
     * @param int $eyeAdaptationValue
     * @param int $currentLightIntensityValue
     * @param int $adaptability
     * @param int $expectedContrast
     */
    public function I_can_create_it_by_extended_rules($eyeAdaptationValue, $currentLightIntensityValue, $adaptability, $expectedContrast)
    {
        $contrast = Contrast::createByExtendedRules(
            $this->createEyeAdaptation($eyeAdaptationValue),
            new LightingQuality($currentLightIntensityValue),
            $raceCode = $this->createRaceCode(),
            $this->createSightRangesTable($raceCode, $adaptability)
        );
        self::assertSame($expectedContrast, $contrast->getValue());
        $asString = (string)$expectedContrast;
        if ($contrast->isFromLightToDark()) {
            $asString .= ' (to dark)';
        }
        if ($contrast->isFromDarkToLight()) {
            $asString .= ' (to light)';
        }
        self::assertSame($asString, (string)$contrast);
        self::assertSame($eyeAdaptationValue > $currentLightIntensityValue, $contrast->isFromLightToDark());
        self::assertSame($eyeAdaptationValue < $currentLightIntensityValue, $contrast->isFromDarkToLight());
    }

    public function provideAdaptationCurrentLightIntensityAndAdaptability()
    {
        return [
            [-123, 345, 10, 47], // from dark to light, rounded up
            [-123, 345, 5, 94], // from dark to light, rounded up
            [678, 1, 10, 67], // from light to dark, rounded down
            [678, 1, 11, 61], // from light to dark, rounded down
            [55, 55, 123, 0], // same
        ];
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|EyeAdaptation
     */
    private function createEyeAdaptation($value)
    {
        $eyeAdaptation = $this->mockery(EyeAdaptation::class);
        $eyeAdaptation->shouldReceive('getValue')
            ->andReturn($value);

        return $eyeAdaptation;
    }

    /**
     * @return \Mockery\MockInterface|RaceCode
     */
    private function createRaceCode()
    {
        return $this->mockery(RaceCode::class);
    }

    /**
     * @param RaceCode $raceCode
     * @param $adaptability
     * @return \Mockery\MockInterface|SightRangesTable
     */
    private function createSightRangesTable(RaceCode $raceCode, $adaptability)
    {
        $sightRangesTable = $this->mockery(SightRangesTable::class);
        $sightRangesTable->shouldReceive('getAdaptability')
            ->with($raceCode)
            ->andReturn($adaptability);

        return $sightRangesTable;
    }
}