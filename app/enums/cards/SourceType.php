<?php


namespace app\enums\cards;


use app\enums\BaseEnum;

class SourceType extends BaseEnum
{
    const STARTER_DECK = 0;
    const LEVEL_UP = 1;
    const EVENT_DROP =2;
    const GATE_DROP = 3;
    const TICKET = 4;
    const CARD_TRADER = 5;
    const EX_CARD_TRADER = 6;
    const MAIN_BOX = 7;
    const MINI_BOX = 8;
    const STRUCTURE_DECK = 9;
    const SPECIAL_DEAL = 10;
    const CAMPAIGN = 11;
}