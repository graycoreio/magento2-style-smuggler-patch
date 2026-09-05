<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\Template;

/**
 * Shared guard for the two places that include a resolved template path.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
trait ChecksTemplatePath
{
    /**
     * Whether the resolved template path is one that must never be included.
     *
     * A stream wrapper reaches outside the filesystem entirely, and a null byte truncates the path
     * that the include actually opens. Neither appears in a legitimate template path.
     *
     * @param string $fileName
     * @return bool
     */
    private function isUnsafeTemplatePath(string $fileName): bool
    {
        $normalized = str_replace('\\', '/', $fileName);

        return strpos($normalized, "\0") !== false || strpos($normalized, '://') !== false;
    }
}
