<?php

namespace Paulo\FileProcessorServer\ValueObjects;

class FileSaverResult
{
    public function __construct(protected array $answer)
    {

    }

    public function toArray()
    {
        return $this->answer;
    }
}