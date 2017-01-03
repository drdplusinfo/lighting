<?php
namespace DrdPlus\Tests\Lighting;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Lighting\EyesAdaptation;
use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Lighting\Opacity;
use DrdPlus\Lighting\Partials\WithInsufficientLightingBonus;
use DrdPlus\Lighting\UnsuitableLightingQualityMalus;
use DrdPlus\Tables\Races\RacesTable;
use DrdPlus\Tables\Races\SightRangesTable;
use Granam\Tests\Tools\TestWithMockery;

class UnsuitableLightingQualityMalusTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideLightingQualityAndExpectedMalus
     * @param int $lightingQualityValue
     * @param int $barrierOpacityValue
     * @param string $raceValue
     * @param int $duskSightBonus
     * @param bool $hasInfravision
     * @param bool $situationAllowsUseOfInfravision
     * @param int $expectedMalus
     */
    public function I_get_malus_from_unsuitable_lighting(
        $lightingQualityValue,
        $barrierOpacityValue,
        $raceValue,
        $duskSightBonus,
        $hasInfravision,
        $situationAllowsUseOfInfravision,
        $expectedMalus
    )
    {
        $unsuitableLightingQualityMalus = UnsuitableLightingQualityMalus::createWithSimplifiedRules(
            new LightingQuality($lightingQualityValue),
            $this->createOpacity($barrierOpacityValue),
            $this->createDuskSight($duskSightBonus),
            $raceCode = RaceCode::getIt($raceValue),
            $subRaceCode = $this->createSubRaceCode(),
            $this->createRacesTable($raceCode, $subRaceCode, $hasInfravision),
            $situationAllowsUseOfInfravision
        );
        self::assertSame($expectedMalus, $unsuitableLightingQualityMalus->getValue());
        self::assertSame((string)$expectedMalus, (string)$unsuitableLightingQualityMalus);
    }

    public function provideLightingQualityAndExpectedMalus()
    {
        // lightingQuality, barrierOpacity, race, duskSightBonus, infravisionCanBeUsed, expectedMalus
        // note: orcs and dwarfs have +4 bonus in darkness, krolls +2 but orcs have -2 malus on bright light
        return [
            [0, -200, RaceCode::HUMAN, 0, false, true, 0],
            [0, 0, RaceCode::HUMAN, 0, false, true, 0],
            [-10, 0, RaceCode::ELF, 0, false, true, 0],
            [-11, 0, RaceCode::HOBBIT, 0, false, true, -1],
            [-11, 0, RaceCode::HOBBIT, 1, false, true, 0],
            [-19, 0, RaceCode::HUMAN, 0, false, true, -1],
            [-20, 0, RaceCode::HUMAN, 0, false, true, -2],
            [-59, 20, RaceCode::ELF, 0, false, true, -7],
            [-59, 0, RaceCode::KROLL, 0, false, true, -3],
            [-59, 0, RaceCode::ORC, 0, true, true, -1],
            [-59, 0, RaceCode::DWARF, 0, true, true, -1],
            [-100, 0, RaceCode::HOBBIT, 0, false, true, -10],
            [-200, 0, RaceCode::HOBBIT, 0, false, false, -20],
            [-200, 0, RaceCode::ORC, 3, true, true, -10],
            [-200, 0, RaceCode::ORC, 3, true, false, -13],
            [-999, 0, RaceCode::DWARF, 90, true, true, -2],
            [-999, 0, RaceCode::DWARF, 90, true, false, -5],
            [-999, 0, RaceCode::DWARF, 0, true, true, -20 /* maximum is -20 */],
            [60, 0, RaceCode::KROLL, 0, false, true, 0],
            [59, 0, RaceCode::ORC, 0, true, true, 0],
            [60, 0, RaceCode::ORC, 0, true, true, -2],
            [61, 1, RaceCode::ORC, 0, true, true, -2],
            [61, 2, RaceCode::ORC, 0, true, true, 0],
            [-999, 0, RaceCode::DWARF, 100000, true, true, 0 /* malus can not turns to bonus */],
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

    /**
     * @return \Mockery\MockInterface|SubRaceCode
     */
    private function createSubRaceCode()
    {
        return $this->mockery(SubRaceCode::class);
    }

    /**
     * @param RaceCode $raceCode
     * @param SubRaceCode $subRaceCode
     * @param $hasInfravision
     * @return \Mockery\MockInterface|RacesTable
     */
    private function createRacesTable(RaceCode $raceCode, SubRaceCode $subRaceCode, $hasInfravision)
    {
        $racesTable = $this->mockery(RacesTable::class);
        $racesTable->shouldReceive('hasInfravision')
            ->with($raceCode, $subRaceCode)
            ->andReturn($hasInfravision);

        return $racesTable;
    }

    /**
     * @test
     * @dataProvider provideEyesAdaptationAndExpectedMalus
     * @param int $eyesAdaptationValue
     * @param int $lightingQualityValue
     * @param int $raceAdaptability
     * @param int $barrierOpacityValue
     * @param string $raceValue
     * @param int $duskSightBonus
     * @param bool $hasInfravision
     * @param bool $situationAllowsUseOfInfravision
     * @param int $expectedMalus
     */
    public function I_get_malus_when_eyes_are_not_yet_adapted(
        $eyesAdaptationValue,
        $lightingQualityValue,
        $raceAdaptability,
        $barrierOpacityValue,
        $raceValue,
        $duskSightBonus,
        $hasInfravision,
        $situationAllowsUseOfInfravision,
        $expectedMalus
    )
    {
        $unsuitableLightingQualityMalus = UnsuitableLightingQualityMalus::createWithEyesAdaptation(
            $this->createEyesAdaptation($eyesAdaptationValue),
            new LightingQuality($lightingQualityValue),
            $this->createOpacity($barrierOpacityValue),
            $this->createDuskSight($duskSightBonus),
            $raceCode = RaceCode::getIt($raceValue),
            $subRaceCode = $this->createSubRaceCode(),
            $this->createSightRangesTable($raceCode, $raceAdaptability),
            $this->createRacesTable($raceCode, $subRaceCode, $hasInfravision),
            $situationAllowsUseOfInfravision
        );
        self::assertSame($expectedMalus, $unsuitableLightingQualityMalus->getValue());
        self::assertSame((string)$expectedMalus, (string)$unsuitableLightingQualityMalus);
    }

    public function provideEyesAdaptationAndExpectedMalus()
    {
        $sightRangesTable = new SightRangesTable();
        // eyesAdaptation, lightingQuality, raceAdaptability, barrierOpacity, race, duskSightBonus,
        // hasInfravision, situationAllowsUseOfInfravision, expectedMalus
        return [
            /**
             * For contrast 80 o bright light example see PPH page 130 right column, @link https://pph.drdplus.jaroslavtyc.com/#postihy_pri_extremne_ostrem_magickem_svetle
             * note: there is an error in the example - dwarf should has malus -3 instead of -4 for contrast 80 ((80-50) / 8 = 3.75 rounded down = 3)
             */
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::HUMAN)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::HUMAN)), 0, RaceCode::HUMAN, 0, false, true, -2],
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::HOBBIT)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::HOBBIT)), 0, RaceCode::HOBBIT, 0, false, true, -2],
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::ELF)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::ELF)), 0, RaceCode::ELF, 0, false, true, -3],
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::KROLL)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::KROLL)), 0, RaceCode::KROLL, 0, false, true, -3],
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::DWARF)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::DWARF)), 0, RaceCode::DWARF, 0, true, true, -3],
            [$sightRangesTable->getMaximalLighting(RaceCode::getIt(RaceCode::ORC)), 80, $sightRangesTable->getAdaptability(RaceCode::getIt(RaceCode::ORC)), 0, RaceCode::ORC, 0, true, true, -5],
            [10, 20, 2, 50, RaceCode::ELF, 12, false, false, -8],
        ];
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|EyesAdaptation
     */
    private function createEyesAdaptation($value)
    {
        $eyesAdaptation = $this->mockery(EyesAdaptation::class);
        $eyesAdaptation->shouldReceive('getValue')
            ->andReturn($value);

        return $eyesAdaptation;
    }

    /**
     * @param RaceCode $raceCode
     * @param int $raceAdaptability
     * @return \Mockery\MockInterface|SightRangesTable
     */
    private function createSightRangesTable(RaceCode $raceCode, $raceAdaptability)
    {
        $sightRangesTable = $this->mockery(SightRangesTable::class);
        $sightRangesTable->shouldReceive('getAdaptability')
            ->with($raceCode)
            ->andReturn($raceAdaptability);

        return $sightRangesTable;
    }
}