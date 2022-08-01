<?php

namespace Custom\Iblock\Import;


use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Custom\Iblock\Import\Importable\IImportable;

class Package implements IPackage
{
    protected \SplQueue $queue;
    protected ErrorCollection $errorCollection;
    
    public function __construct()
    {
        $this->queue = new \SplQueue();
        $this->errorCollection = new ErrorCollection();
    }

    public function append(IImportable $element)
    {
        $this->queue->enqueue($element);
    }

    public function retrieve(): ?IImportable
    {
        return $this->queue->isEmpty() ? null : $this->queue->dequeue();
    }

    public function getIterator(): \Generator
    {
        $this->queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_KEEP);
        yield from $this->queue;
    }
    
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): ?Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}