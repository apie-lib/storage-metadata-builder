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
        $current = isset($this->_indexes) ? Utils::toArray($this->_indexes) : [];
        $offset = 0;
        $refProperty = 'ref_' . (new ReflectionClass($this))->getShortName();
        $total = array_reduce(
            $indexes,
            function (float $carry, float $item) {
                return $carry + $item;
            },
            0
        );
        foreach ($indexes as $search => $tf) {
            if (isset($current[$offset])) {
                if ($current[$offset]->text !== $search || $current[$offset]->tf !== $tf) {
                    $current[$offset]->text = $search;
                    $current[$offset]->priority = 1;
                    $current[$offset]->idf = 1;
                    $current[$offset]->tf = $tf / $total;
                }
            } else {
                $current[$offset] = $this->getIndexTable()->newInstance($search, 1, 1, $tf / $total);
            }
            
            $current[$offset]->$refProperty = $this;
            $offset++;
        }
        for (;$offset < count($current);$offset++) {
            $current[$offset]->$refProperty = null;
        }
        $current = array_slice($current, 0, $offset);
        $this->_indexes = ConverterUtils::dynamicCast($current, (new ReflectionProperty($this, '_indexes'))->getType());
    }
}
