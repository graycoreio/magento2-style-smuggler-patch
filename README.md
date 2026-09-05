# Style Smuggler Patch

<div align="center">

[![Packagist Downloads](https://img.shields.io/packagist/dm/graycore/style-smuggler-patch?color=blue)](https://packagist.org/packages/graycore/style-smuggler-patch/stats)
[![Packagist Version](https://img.shields.io/packagist/v/graycore/style-smuggler-patch?color=blue)](https://packagist.org/packages/graycore/style-smuggler-patch)
[![Packagist License](https://img.shields.io/packagist/l/graycore/style-smuggler-patch)](https://github.com/graycoreio/style-smuggler-patch/blob/master/LICENSE)
[![MageCheck Status](https://img.shields.io/github/actions/workflow/status/graycoreio/style-smuggler-patch/check-extension.yaml?&label=MageCheck&labelColor=1a1a1a)](https://github.com/graycoreio/style-smuggler-patch/actions/workflows/check-extension.yaml)
![MageCheck Supported Version](https://img.shields.io/badge/currently_supported-any?label=MageCheck%20Supported&labelColor=1a1a1a&color=090c9b)

</div>

> [!CAUTION]
> **This module deliberately breaks checkout for Payflow Pro and Payments Pro over GraphQL.**
>
> It does not sanitize, filter, or validate anything. It replaces the `handlePayflowProResponse`
> resolver with one that throws immediately. Every call to that mutation fails, for attackers and
> for real shoppers alike. If your storefront takes payments through Payflow Pro or Payments Pro on
> GraphQL, installing this module will stop those orders from completing. That is the intended
> behavior — it is a deliberate trade of revenue for exposure. Make that trade knowingly.
>
> **This is not a complete fix.**
>
> * It is **not** an official Adobe patch and carries no warranty. See the [LICENSE](LICENSE).
> * It closes the `/graphql` mutation **only**.
> * The mitigation will change as better fixes are found. Read the [CHANGELOG](CHANGELOG.md) before
>   every upgrade.
>
> **Test on a staging environment first. Have a rollback plan.**

## Magento Version Support
![Magento v2.4 Supported](https://img.shields.io/badge/Magento-2.4-brightgreen.svg?labelColor=2f2b2f&logo=magento&logoColor=f26724&color=464246&longCache=true&style=flat)

## Purpose
This repo creates a stop-gap patch for the [Style Smuggler](https://sansec.io/research/stylesmuggler) vulnerability. It's purely mitigation. It likely isn't perfect, but it's my current best assessment of how to mitigate the vulnerability.

This package will change versions as I trial different layers of fixes to the vulnerability.

## Getting Started
This module is intended to be installed with [composer](https://getcomposer.org/). From the root of your Magento 2 project:

1. Download the package
```bash
composer require graycore/style-smuggler-patch
```
2. Enable the package

```bash
./bin/magento module:enable Graycore_StyleSmugglerPatch
```

## Upgrading
* [Semver Policy](https://semver.org/)
