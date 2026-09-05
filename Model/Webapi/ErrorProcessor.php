<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\Webapi;

use Magento\Framework\Webapi\ErrorProcessor as WebapiErrorProcessor;

/**
 * Neutralizes PHP open tags in fatal error reports written to var/report/api.
 *
 * A report is attacker-influenced content on disk. Paired with a local file include it becomes
 * code execution, so the open tag is broken before the report is serialized. Sanitizing the input
 * is equivalent to sanitizing the serialized output here: Json::serialize() is json_encode()
 * without JSON_HEX_TAG, which leaves "<" and "?" untouched.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class ErrorProcessor extends WebapiErrorProcessor
{
    /**
     * Log information about fatal error.
     *
     * @param string $reportData
     * @return string
     */
    protected function _saveFatalErrorReport($reportData)
    {
        if (is_string($reportData)) {
            $reportData = str_replace('<?', '< ?', $reportData);
        }

        return parent::_saveFatalErrorReport($reportData);
    }
}
