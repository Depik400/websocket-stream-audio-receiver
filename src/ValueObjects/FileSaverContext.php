<?php

namespace Paulo\FileProcessorServer\ValueObjects;

use Paulo\FileProcessorServer\Enum\FileServiceEventType;

class FileSaverContext
{
    public function __construct(
        public FileServiceEventType $state,
    ) {

    }

    public function setState(FileServiceEventType $state)
    {
        $this->state = $state;

    }
}