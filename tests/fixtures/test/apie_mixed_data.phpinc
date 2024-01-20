<?php
// @codingStandardsIgnoreStart
class apie_mixed_data implements Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface
{
	public ?string $serializedString;
	public ?string $originalType;
	public mixed $unserializedObject;


	public function __construct($input)
	{
		$this->unserializedObject = $input;
		$this->serializedString = serialize($input);
		$this->originalType = get_debug_type($input);
	}


	public function toOriginalObject(): mixed
	{
		if (!isset($this->unserializedObject)) {
		    $this->unserializedObject = unserialize($this->serializedString);
		    if (get_debug_type($this->unserializedObject) !== $this->originalType) {
		        throw new \LogicException("Could not unserialize object again");
		    }
		}
		return $this->unserializedObject;
	}
}
