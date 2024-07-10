<?php

namespace Paulo\FileProcessorServer\Events;

use Paulo\FileProcessorServer\Enum\FileServiceEventType;

class FileServiceEvent
{
    public function __construct(protected FileServiceEventType $type, protected $data)
    {

    }

    public function getType(): FileServiceEventType
    {
        return $this->type;
    }

    public function getArrayData(): array
    {
        return json_decode($this->data, true);
    }

    public function getRawData(): string
    {
        return $this->data;
    }
}