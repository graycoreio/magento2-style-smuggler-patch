<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\Template;

/**
 * The set of block classes an email template {{block}} directive may instantiate.
 *
 * The list is empty by default, so no template can instantiate any block until a merchant opts a
 * class in. Add entries from your own module's di.xml:
 *
 *     <type name="Graycore\StyleSmugglerPatch\Model\Template\BlockDirectiveAllowList">
 *         <arguments>
 *             <argument name="allowedClasses" xsi:type="array">
 *                 <item name="cms_block" xsi:type="string">Magento\Cms\Block\Block</item>
 *             </argument>
 *         </arguments>
 *     </type>
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class BlockDirectiveAllowList
{
    /**
     * Normalized allowed class names, as a lookup map.
     *
     * @var array<string, bool>
     */
    private $allowed = [];

    /**
     * @param string[] $allowedClasses
     */
    public function __construct(array $allowedClasses = [])
    {
        foreach ($allowedClasses as $class) {
            if (!is_string($class)) {
                continue;
            }
            $normalized = $this->normalize($class);
            if ($normalized !== '') {
                $this->allowed[$normalized] = true;
            }
        }
    }

    /**
     * Whether a template directive may instantiate the given block class.
     *
     * Matching is on the exact class name, so a subclass of an allowed class is not itself allowed.
     *
     * @param string $class
     * @return bool
     */
    public function isAllowed(string $class): bool
    {
        if ($this->isAlwaysRefused($class)) {
            return false;
        }

        return isset($this->allowed[$this->normalize($class)]);
    }

    /**
     * Whether the class is off limits to a template directive no matter what the allowlist says.
     *
     * @param string $class
     * @return bool
     */
    public function isAlwaysRefused(string $class): bool
    {
        $normalized = $this->normalize($class);
        if ($normalized === '') {
            return false;
        }
        if (stripos($normalized, '\\Block\\Adminhtml\\') !== false) {
            return true;
        }

        return stripos($normalized, 'Magento\\Backend\\Block\\') === 0;
    }

    /**
     * Canonicalize a class name so alternate spellings compare equal.
     *
     * Unifies / to \, collapses repeated separators and drops leading ones. The result is
     * lowercased because PHP resolves class names case-insensitively.
     *
     * @param string $class
     * @return string
     */
    private function normalize(string $class): string
    {
        $normalized = str_replace('/', '\\', trim($class));
        while (strpos($normalized, '\\\\') !== false) {
            $normalized = str_replace('\\\\', '\\', $normalized);
        }

        return strtolower(ltrim($normalized, '\\'));
    }
}
