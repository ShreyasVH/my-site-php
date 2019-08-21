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
}