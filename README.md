Magento Gifts
=============

Magento Gifts is a simple Magento module for promotions purposes. Very similiar to [shopping cart price rules](http://www.magentocommerce.com/knowledge-base/entry/what-are-shopping-cart-price-rules-and-how-do-i-use-them). But instead of discounts your able to offer gifts.

Prerequisites
-------------

* Magento Community Edition 1.7+

Installation
--------------

1. Copy the content of the `src` folder into the root folder of your Magento installation. 
2. Clear the following caches under `System > Cache Management` in the backend if caching is enabled:
 * `Configuration`
 * `Layouts`
 * `Translations`
 * `EAV types and attributes`
3. Grant access to the module for other users beside the `Administrators` under `System > Permissions > Roles` in the backend, by activating `Promotions > Shopping Cart Gift Rules`, if neccessary.

Usage
-----

To create, edit and delete shopping cart gift rules go under `Promotions > Shopping Cart Gift Rules` in the backend. To apply a shopping cart gift rule, make sure that in the edit form: 

* `Status` is `Active`,
* `Customer Group` is set, e.g. `NOT LOGGED IN`,
* `From Date` and `To Date` be true for the present day,
* `Conditions` be applicable to your needs, see [Magento Knowledge Base](http://www.magentocommerce.com/knowledge-base/entry/tip-applying-promotion-to-multiple-skus),
* `Qty` is greater than `0`,
* `All` in the first column is selected to search, and select at least one product as a gift.

Limitations
-----------

* Only simple products are supported as gifts.
* Coupon codes are not supported.

Contribution
------------

Please report issues on the GitHub [issue tracker](https://github.com/witrin/magento-gifts/issues). Personal emails are not appropriate for bug reports. Patches are preferred as GitHub pull requests.

License
-------

Licensed under the [Open Software License version 3.0](http://opensource.org/licenses/osl-3.0).