Gift Rules
==========

An easy to use Magento module for promotion purposes, very similiar to [shopping cart price rules](http://www.magentocommerce.com/knowledge-base/entry/what-are-shopping-cart-price-rules-and-how-do-i-use-them). But instead of discounts you are able to offer gifts.

This module comes with its own rule entity, no rewriting of the Magento core distribution.

Prerequisites
-------------

The minimum requirements for this extension to run are:

* Magento Community Edition 1.8

Installation
------------

1. Copy the content of the `src` folder into the root folder of your Magento installation.
2. Clear the following caches under `System > Cache Management` when caching is enabled:
 * `Configuration`,
 * `Layouts`,
 * `Translations`,
 * and `EAV types and attributes`.
3. Grant access to the module for other users beside the `Administrators` under `System > Permissions > Roles`, by activating `Promotions > Shopping Cart Gift Rules`, if needed.

Usage
-----

To create, edit and delete shopping cart gift rules go under `Promotions > Shopping Cart Gift Rules`. To apply a shopping cart gift rule, make sure that in the edit form: 

* `Status` is `Active`,
* `Customer Group` is set, e.g. `NOT LOGGED IN`,
* `From Date` and `To Date` be true for the present day,
* `Conditions` be applicable to your needs,
* `Qty` is greater than `0`,
* and at least one simple product is selected as a gift.

Limitations
-----------

Currently, this module is subject to some restrictions:

* only simple products are supported as gifts,
* and coupon codes are not supported.

Contribution
------------

Please report issues on the GitHub [issue tracker](https://github.com/witrin/magento-gifts/issues). **Personal emails are not appropriate for bug reports.** Contributions are preferred as GitHub pull requests.

License
-------

Licensed under the [Open Software License version 3.0](http://opensource.org/licenses/osl-3.0).