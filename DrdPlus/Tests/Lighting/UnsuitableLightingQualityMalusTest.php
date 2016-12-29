<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Lighting\Opacity;
use DrdPlus\Lighting\Partials\WithInsufficientLightingBonus;
use DrdPlus\Lighting\UnsuitableLightingQualityMalus;
use Granam\Tests\Tools\TestWithMockery;

class UnsuitableLightingQualityMalusTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideLightingQualityAndExpectedMalus
     * @param int $lightingQualityValue
     * @param int $barrierOpacityValue
     * @param string $raceValue
     * @param int $fromDuskSightBonus
     * @param bool $infravisionCanBeUsed
     * @param int $expectedMalus
     */
    public function I_get_malus_from_insufficient_light(
        $lightingQualityValue,
        $barrierOpacityValue,
        $raceValue,
        $fromDuskSightBonus,
        $infravisionCanBeUsed,
        $expectedMalus
    )
    {
        $insufficientLightingQualityMalus = new UnsuitableLightingQualityMalus(
            new LightingQuality($lightingQualityValue),
            $this->createOpacity($barrierOpacityValue),
            RaceCode::getIt($raceValue),
            $this->createDuskSight($fromDuskSightBonus),
            $infravisionCanBeUsed
        );
        self::assertSame($expectedMalus, $insufficientLightingQualityMalus->getValue());
        self::assertSame((string)$expectedMalus, (string)$insufficientLightingQualityMalus);
    }

    public function provideLightingQualityAndExpectedMalus()
    {
        // note: orcs and dwarfs have +4 bonus in darkness, krolls +2 but orcs have -2 malus on bright light
        return [
            [0, -200, RaceCode::HUMAN, 0, true, 0],
            [0, 0, RaceCode::HUMAN, 0, true, 0],
            [-10, 0, RaceCode::ELF, 0, true, 0],
            [-11, 0, RaceCode::HOBBIT, 0, true, -1],
            [-11, 0, RaceCode::HOBBIT, 1, true, 0],
            [-19, 0, RaceCode::HUMAN, 0, true, -1],
            [-20, 0, RaceCode::HUMAN, 0, true, -2],
            [-59, 20, RaceCode::ELF, 0, true, -7],
            [-59, 0, RaceCode::KROLL, 0, true, -3],
            [-59, 0, RaceCode::ORC, 0, true, -1],
            [-59, 0, RaceCode::DWARF, 0, true, -1],
            [-100, 0, RaceCode::HOBBIT, 0, true, -10],
            [-200, 0, RaceCode::HOBBIT, 0, false, -20],
            [-200, 0, RaceCode::ORC, 3, true, -10],
            [-200, 0, RaceCode::ORC, 3, false, -13],
            [-999, 0, RaceCode::DWARF, 90, true, -2],
            [-999, 0, RaceCode::DWARF, 90, false, -5],
            [-999, 0, RaceCode::DWARF, 0, true, -20 /* maximum is -20 */],
            [60, 0, RaceCode::KROLL, 0, true, 0],
            [59, 0, RaceCode::ORC, 0, true, 0],
            [60, 0, RaceCode::ORC, 0, true, -2],
            [61, 1, RaceCode::ORC, 0, true, -2],
            [61, 2, RaceCode::ORC, 0, true, 0],
            [-999, 0, RaceCode::DWARF, 100000, true, 0 /* malus can not turns to bonus */],
        ];
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|Opacity
     */
    private function createOpacity($value)
    {
        $opacity = $this->mockery(Opacity::class);
        $opacity->shouldReceive('getValue')
            ->andReturn($value);

        return $opacity;
    }

    /**
     * @param int $bonus
     * @return \Mockery\MockInterface|WithInsufficientLightingBonus
     */
    private function createDuskSight($bonus)
    {
        $duskSight = $this->mockery(WithInsufficientLightingBonus::class);
        $duskSight->shouldReceive('getInsufficientLightingBonus')
            ->andReturn($bonus);

        return $duskSight;
    }
}