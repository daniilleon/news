<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerKWUSDw5\Module_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerKWUSDw5/Module_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerKWUSDw5.legacy');

    return;
}

if (!\class_exists(Module_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerKWUSDw5\Module_KernelDevDebugContainer::class, Module_KernelDevDebugContainer::class, false);
}

return new \ContainerKWUSDw5\Module_KernelDevDebugContainer([
    'container.build_hash' => 'KWUSDw5',
    'container.build_id' => 'b9b1a2d7',
    'container.build_time' => 1732145998,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerKWUSDw5');
