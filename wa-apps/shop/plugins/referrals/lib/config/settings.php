<?php

$_locale = wa()->getLocale();

return array(
    'enabled' => array(
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ),
    'activity' => array(
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ),
    'rate' => array(
        'value' => 100
    ),
    'cookie_expire' => array(
        'value' => 30
    ),
    'cookie_main_domain' => array(
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'notifications' => array(
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ),
    'promo' => array(
        'value' => ($_locale == 'ru_RU') ?
                    '<p>Наша реферальная программа позволит вам зарабатывать, привлекая своих друзей и знакомых в наш интернет-магазин.</p>
<p>Вступайте в реферальную программу сегодня и получите свою уникальную партнерскую ссылку — вы сможете отправлять ее своим знакомым, публиковать в соцсетях, на сайтах, блогах и форумах. За каждого покупателя, который придет в наш интернет-магазин по вашей ссылке и оформит заказ, вы получите <b>ПРОЦЕНТ_ВОЗНАГРАЖДЕНИЯ% от стоимости заказа</b>! Заработанные баллы можно потратить на покупки в нашем магазине! Кроме того, мы предлагаем вам гибкие условия выплат: ОПИШИТЕ ЗДЕСЬ УСЛОВИЯ ВЫПЛАТ.</p>'
                    :
                    '<p>Our referral program will help you earn money by referring new customers to our online store.</p>
<p>Enroll today and get your unique promo link, which you can send to your friends or publish on any website. For everyone who follows your referral link and places an order, we will credit you <b>ENTER_YOUR_VALUE% of the order value</b>! Earned bonuses can be spent for your own purchases in our store. We also offer you flexible payout conditions: DESCRIBE YOUR PAYOUT CONDITIONS</p>'
    ),
    'terms' => array(
        'value' => ($_locale == 'ru_RU') ?
                    "<h1>УСЛОВИЯ РЕФЕРАЛЬНОЙ ПРОГРАММЫ</h1>
<p>Настоящее партнерское соглашение (далее «Соглашение») является договором-офертой между вами (далее «Партнер») и [НАЗВАНИЕ КОМПАНИИ] (далее «Компания») и устанавливает приведенные ниже условия сотрудничества.</p>
<ol>
    <li>Компания предоставляет Партнеру персональный промо-код, который является уникальным для Партнера и позволяет однозначно идентифицировать Партнера при выполнении любых операций, связанных с использованием промо-кода на сайте Компании по адресу [www.yourdomain.ru]. Для упрощения использования промо-кода Партнеру предоставляется специальная ссылка на сайт Компании, после перехода по которой промо-код автоматически начинает использоваться при оформлении заказа привлеченным покупателем.</li>
    <li>Любой заказ с использованием промо-кода считается заказом, связанным с тем Партнером, промо-код которого был использован при оформлении заказа.</li>
    <li>Компания производит начисление вознаграждения Партнеру в размере [УКАЖИТЕ ПРОЦЕНТ]% от общей стоимости покупки, выполненной с использованием его промо-код.</li>
    <li>Партнер имеет право покупать товары на сайте Компании с использованием собственного промо-кода, но в этом случае Компания не будет производить начисление вознаграждения Партнеру.</li>
    <li>Выплаты осуществляются с помощью следующих способов: [ПЕРЕЧИСЛИТЕ СПОСОБЫ ВЫПЛАТ ВОЗНАГРАЖДЕНИЯ]. Никакие другие способы выплат не предусмотрены. Партнер также имеет возможность использовать накопленное вознаграждение для оплаты собственных покупок на сайте Компании.</li>
    <li>Компания предоставляет Партнеру доступ к информации о начисленных ему вознаграждениях и произведенных выплатах на специальной веб-странице в личном кабинете на сайте Компании.</li>
    <li>Партнер имеет право распространять предоставленный ему промо-код любым доступным ему способом.</li>
    <li>Партнер обязуется не рекламировать товары Компании способами, которые могут расцениваться как спам, а также иными методами, дискредитирующими Компанию. Партнер признает, что жалобы на него со стороны покупателей Компании могут повлечь за собой прекращение действия данного Соглашения.</li>
</ol>"
                    :
                    "<h1>REFERRAL PROGRAM TERMS AND CONDITIONS</h1>
<p>This partnership agreement (hereinafter referred to as “Agreement”) is a public offer between you (hereinafter referred to as “Partner”) and [COMPANY NAME] (hereinafter referred to as “Company”), and sets the co-operation conditions as set out below.</p>
<ol>
	<li>Company provides a unique personal promo code to Partner, which enables Company to identify Partner uniquely in the course of all actions, in which the code is involved, on Company's website at [www.yourdomain.com]. To facilitate the transmission and use of the promo code, Partner will receive the code as part of a special link pointing Company's website, which will automatically make the code taken into account when a customer follows the link and places an order on Company's website.</li>
	<li>Any order placed on Company's website with the use of a promo code will be associated with the Partner whose promo code was used by a customer during checkout.</li>
	<li>Company shall credit commission to Partner at the amount of [SPECIFY PERCENTAGE]% of the total cost of the order placed with the use of Partner's promo code.</li>
	<li>Partner may purchase any products on Company's website using his own promo code, but no commission will be credited to Partner's account in this case.</li>
	<li>Accrued commissions will be paid to Partner only via [LIST COMMISSION PAYMENT OPTIONS]. No other payment options are available. Partner may also use accrued bonus points to pay for his own orders on Company's website.</li>
	<li>Company shall provide Partner with access to statistical information on the accrued commission and the payouts made to Partner by means of a special web page in his online account on Company's website.</li>
	<li>Partner may publish the promo code anywhere in any suitable manner.</li>
	<li>Partner may offer his promo code any number of times to the same person or to an unlimited number of Company's customers.</li>
	<li>Partner guarantees not to advertise Company's products using methods which may be classified as spam or in any manner compromising Company's reputation. Partner agrees that any claims made by Company's customers in relation to Partner may result in a premature termination of this Agreement.</li>
</ol>"
    ),
);