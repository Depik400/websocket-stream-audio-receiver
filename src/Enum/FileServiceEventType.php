<?php

namespace Paulo\FileProcessorServer\Enum;

enum FileServiceEventType: string
{
    case Init = 'init';
    case Data = 'data';
    case Close = 'close';
}