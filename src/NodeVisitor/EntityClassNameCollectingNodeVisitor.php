<?php

declare(strict_types=1);

namespace TomasVotruba\Finalize\NodeVisitor;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

final class EntityClassNameCollectingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private array $entityClassNames = [];

    public function enterNode(Node $node)
    {
        if (! $node instanceof Class_) {
            return null;
        }

        // must be named
        if (! $node->namespacedName instanceof Node\Name) {
            return null;
        }

        if ($this->hasEntityDocBlock($node)) {
            $this->entityClassNames[] = $node->namespacedName->toString();
            return null;
        }

        if ($this->hasEntityAttribute($node)) {
            $this->entityClassNames[] = $node->namespacedName->toString();
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getEntityClassNames(): array
    {
        $uniqueEntityClassNames = array_unique($this->entityClassNames);
        sort($uniqueEntityClassNames);

        return $uniqueEntityClassNames;
    }

    private function hasEntityDocBlock(Class_ $node): bool
    {
        $docComment = $node->getDocComment();
        if ($docComment instanceof Doc) {
            // dummy check
            if (! str_contains($docComment->getText(), '@')) {
                return false;
            }

            if (str_contains($docComment->getText(), 'Entity')) {
                return true;
            }

            if (str_contains($docComment->getText(), 'Embeddable')) {
                return true;
            }
        }

        return false;
    }

    private function hasEntityAttribute(Class_ $class): bool
    {
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (str_ends_with($attr->name->toString(), 'Entity')) {
                    return true;
                }

                if (str_ends_with($attr->name->toString(), 'Embeddable')) {
                    return true;
                }
            }
        }

        return false;
    }
}
