<?php

namespace Paulo\FileProcessorServer\Services;

use Paulo\FileProcessorServer\Enum\FileServiceEventType;
use Paulo\FileProcessorServer\Events\FileServiceEvent;
use Paulo\FileProcessorServer\ValueObjects\FileSaverContext;
use Paulo\FileProcessorServer\ValueObjects\FileSaverResult;

class FileServerService
{
    protected \SplFileObject $file;
    public function __construct(public FileSaverContext $fileSaverContext)
    {

    }

    public function processEvent(FileServiceEvent $fileServiceEvent): ?FileSaverResult
    {
        return match ($fileServiceEvent->getType()) {
            FileServiceEventType::Init => $this->processInit($fileServiceEvent),
            FileServiceEventType::Data => $this->processData($fileServiceEvent),
            FileServiceEventType::Close => $this->processClose($fileServiceEvent),
        };
    }

    protected function processInit(FileServiceEvent $fileServiceEvent): ?FileSaverResult
    {
        $this->fileSaverContext->setState(FileServiceEventType::Data);
        if (file_exists(base_path('files/file.webm'))) {
            unlink(base_path('files/file.webm'));
        }
        $this->file = new \SplFileObject(base_path('files/file.webm'), 'wb+');
        echo 'Init file' . PHP_EOL;
        return new FileSaverResult([
            'status' => 'init'
        ]);
    }


    protected function processData(FileServiceEvent $fileServiceEvent): ?FileSaverResult
    {
        $this->fileSaverContext->setState($fileServiceEvent->getType());
        echo PHP_EOL . 'data is vailable' . PHP_EOL;
        $this->file->fwrite($fileServiceEvent->getRawData());
        return new FileSaverResult([
            'status' => 'progress'
        ]);
    }

    protected function processClose(FileServiceEvent $fileServiceEvent): ?FileSaverResult
    {
        $this->fileSaverContext->setState($fileServiceEvent->getType());
        unset($this->file);
        echo 'End file' . PHP_EOL;
        return new FileSaverResult([
            'status' => 'finished'
        ]);
    }
}