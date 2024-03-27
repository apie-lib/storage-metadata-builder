<?php
namespace Apie\StorageMetadataBuilder\CodeGenerators;

use Apie\Core\Permissions\PermissionInterface;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\StorageMetadata\Attributes\AclLinkAttribute;
use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadataBuilder\Interfaces\BootGeneratedCodeInterface;
use Apie\StorageMetadataBuilder\Interfaces\PostRunGeneratedCodeContextInterface;
use Apie\StorageMetadataBuilder\Interfaces\RootObjectInterface;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCode;
use Apie\StorageMetadataBuilder\Mediators\GeneratedCodeContext;
use Nette\PhpGenerator\ClassType;
use ReflectionClass;

final class AccessControlListGenerator implements BootGeneratedCodeInterface, PostRunGeneratedCodeContextInterface
{
    public function boot(GeneratedCode $generatedCode): void
    {
        $class = new ClassType('apie_access_control_list');
        $constructor = $class->addMethod('__construct');
        $constructor->addPromotedParameter('permission')
            ->setType('string');
        $generatedCode->generatedCodeHashmap['apie_access_control_list'] = $class;
    }

    public function postRun(GeneratedCodeContext $generatedCodeContext): void
    {
        $accessControlTable = $generatedCodeContext->generatedCode->generatedCodeHashmap['apie_access_control_list'] ?? null;
        assert($accessControlTable instanceof ClassType);
        $added = false;
        foreach ($generatedCodeContext->generatedCode->generatedCodeHashmap->getObjectsWithInterface(RootObjectInterface::class) as $code) {
            if ($this->hasPermissionRequirement($code)) {
                $added = true;
                $code->addMethod('getAccessControlTable')
                    ->setReturnType('ReflectionClass')
                    ->setBody('return new \\ReflectionClass(' . $accessControlTable->getName() . '::class);');
                $code->addProperty('_acl')
                    ->setType('array')
                    ->addAttribute(AclLinkAttribute::class, [$accessControlTable->getName()]);
                $accessControlTable->addProperty('ref_' . $code->getName(), null)
                    ->setType('?' . $code->getName())
                    ->addAttribute(ManyToOneAttribute::class, ['_acl']);
            }
        }
        if (!$added) {
            unset($generatedCodeContext->generatedCode->generatedCodeHashmap['apie_access_control_list']);
        }
    }

    private function hasPermissionRequirement(ClassType $code): bool
    {
        // comment is original php class
        $doc = $code->getComment();
        if (!class_exists($doc)) {
            return false;
        }
        $refl = new ReflectionClass($doc);
        return in_array(RequiresPermissionsInterface::class, $refl->getInterfaceNames());
    }
}