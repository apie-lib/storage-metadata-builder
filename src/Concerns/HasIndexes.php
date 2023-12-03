<?php
namespace Apie\StorageMetadataBuilder\Concerns;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Utils;
use ReflectionClass;
use ReflectionProperty;

trait HasIndexes
{
    abstract public function getIndexTable(): ReflectionClass;

    /**
     * @param array<string, int> $indexes
     */
    public function replaceIndexes(array $indexes): void
    {
        $current = isset($this->_index) ? Utils::toArray($this->_index) : [];
        $offset = 0;
        foreach ($indexes as $search => $priority) {
            if (isset($current[$offset])) {
                if ($current[$offset]->search !== $search || $current[$offset]->priority !== $priority) {
                    $current[$offset]->search = $search;
                    $current[$offset]->priority = $priority;
                    $current[$offset]->idf = null;
                    $current[$offset]->tdf = null;
                }
            } else {
                $current[$offset] = $this->getIndexTable()->newInstance($search, $priority);
            }
            $offset++;
        }
        $current = array_slice($current, 0, $offset);
        $this->_indexes = ConverterUtils::dynamicCast($current, (new ReflectionProperty($this, '_indexes'))->getType());
    }
}
