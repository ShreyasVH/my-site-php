<?php


namespace app\enums\cards;


use app\enums\BaseEnum;

class CardSubType extends BaseEnum
{
    const NORMAL = 0;
    const EFFECT = 1;
    const RITUAL = 2;
    const FUSION = 3;
    const SYNCHRO = 4;
    const TOON = 5;
    const GEMINI = 6;
    const UNION = 7;
    const SPIRIT = 8;
    const TUNER = 9;
    const FLIP = 10;
    const FIELD = 11;
    const EQUIP = 12;
    const CONTINUOUS = 13;
    const QUICK_PLAY = 14;
    const COUNTER = 15;
    const XYZ = 16;
}