<?php
namespace DrdPlus\Tests\Lighting;

use Doctrineum\Tests\Entity\AbstractDoctrineEntitiesTest;
use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Lighting\Contrast;
use DrdPlus\Lighting\Glare;
use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Properties\Base\Knack;
use DrdPlus\Properties\Derived\Senses;
use DrdPlus\RollsOn\Traps\RollOnSenses;
use DrdPlus\Tables\Races\RacesTable;

class LightingDoctrineEntitiesTest extends AbstractDoctrineEntitiesTest
{
    protected function getDirsWithEntities()
    {
        return __DIR__ . '/../../Lighting';
    }

    protected function createEntitiesToPersist()
    {
        return [
            new Glare(
                new Contrast(new LightingQuality(213), new LightingQuality(569)),
                new RollOnSenses(
                    new Senses(Knack::getIt(1), RaceCode::getIt(RaceCode::ELF), SubRaceCode::getIt(SubRaceCode::DARK), new RacesTable()),
                    Roller2d6DrdPlus::getIt()->roll()
                ),
                false
            )
        ];
    }

}