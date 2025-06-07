- 根据银行卡获取银行名称
```php
$a = \vring\validate\AliBankCard();
$a = $a->getBinkType('6228480000000000');
/**
Array
(
    [cardType] => DC
    [bank] => CMB
    [key] => 6214832018989180
    [messages] => Array
        (
        )

    [validated] => 1
    [stat] => ok
    [bank_text] => 招商银行
)

 */
//验证手机号
$alic->verify('6214832018989180');
```