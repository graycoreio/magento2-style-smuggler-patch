# Style Smuggler Patch

<div align="center">

[![Packagist Downloads](https://img.shields.io/packagist/dm/graycore/magento2-style-smuggler-patch?color=blue)](https://packagist.org/packages/graycore/magento2-style-smuggler-patch/stats)
[![Packagist Version](https://img.shields.io/packagist/v/graycore/magento2-style-smuggler-patch?color=blue)](https://packagist.org/packages/graycore/magento2-style-smuggler-patch)
[![Packagist License](https://img.shields.io/packagist/l/graycore/magento2-style-smuggler-patch)](https://github.com/graycoreio/magento2-style-smuggler-patch/blob/master/LICENSE)
[![MageCheck Status](https://img.shields.io/github/actions/workflow/status/graycoreio/magento2-style-smuggler-patch/check-extension.yaml?&label=MageCheck&labelColor=1a1a1a)](https://github.com/graycoreio/magento2-style-smuggler-patch/actions/workflows/check-extension.yaml)
![MageCheck Supported Version](https://img.shields.io/badge/currently_supported-any?label=MageCheck%20Supported&labelColor=1a1a1a&color=090c9b)

</div>

> [!CAUTION]
> **This is an unofficial stop-gap, not an official Adobe patch, and it carries no warranty.**
> See the [LICENSE](LICENSE).
>
> It hardens three points on the StyleSmuggler chain: the email template `{{block}}` directive
> instantiates only allowlisted block classes and nothing is allowlisted by default, the grid row
> URL generator factory validates the class before building it, and Web API fatal error reports
> have their PHP open tags broken. That is hardening, not a fix — the vulnerability itself is
> unpatched, and other paths through it remain open.
>
> A vulnerable store may already be compromised. Mitigating an entry point does **not** remove a
> backdoor that is already there. Audit your store.
>
> The mitigation will change as better fixes are found. Read the [CHANGELOG](CHANGELOG.md) before
> every upgrade. **Test on a staging environment first. Have a rollback plan.**

## Magento Version Support
![Magento v2.4 Supported](https://img.shields.io/badge/Magento-2.4-brightgreen.svg?labelColor=2f2b2f&logo=magento&logoColor=f26724&color=464246&longCache=true&style=flat)

## Purpose
This repo creates a stop-gap patch for the [Style Smuggler](https://sansec.io/research/stylesmuggler) vulnerability. It's purely mitigation. It likely isn't perfect, but it's my current best assessment of how to mitigate the vulnerability.

This package will change versions as I trial different layers of fixes to the vulnerability.

## Getting Started
This module is intended to be installed with [composer](https://getcomposer.org/). From the root of your Magento 2 project:

1. Download the package
```bash
composer require graycore/magento2-style-smuggler-patch
```
2. Enable the package

```bash
./bin/magento module:enable Graycore_StyleSmugglerPatch
```

3. Check your logs for refused blocks

The `{{block}}` email template directive can no longer instantiate any block class. If your
transactional emails need one, the refusal is logged as `critical` with the class name, so run a
test send and read `var/log/system.log` before going live:

```
Refused a block class in an email template {{block}} directive because it is not on the allowlist. {"class":"Vendor\\Module\\Block\\OrderSummary"}
```

## Allowlisting a Block Class
Add the classes your email templates need from your own module's `di.xml`:

```xml
<type name="Graycore\StyleSmugglerPatch\Model\Template\BlockDirectiveAllowList">
    <arguments>
        <argument name="allowedClasses" xsi:type="array">
            <item name="order_summary" xsi:type="string">Vendor\Module\Block\OrderSummary</item>
        </argument>
    </arguments>
</type>
```

Then `bin/magento cache:clean config` (or `setup:di:compile` in production mode).

A few things to know:

* Matching is on the exact class name, so a subclass of an allowlisted class is not itself
  allowlisted. Separator spelling, leading separators and case do not matter.
* Backend blocks — anything under `Magento\Backend\Block\` or a `\Block\Adminhtml\`
  namespace — are refused even if you allowlist them.
* The list lives on the filesystem rather than in store configuration on purpose: widening it
  should take a deploy, not admin or database access.
* `{{block id="..."}}` is untouched. It names no class for Magento to resolve; core renders a
  CMS block by id.

## Upgrading
* [Semver Policy](https://semver.org/)
