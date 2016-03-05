<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
return array(
    "mask" => array(
        ">num" => array(
            "module" => "more",
            "description" => "При покупке больше X любых единиц товара устанавливается скидка на все товары",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            ),
            "discountEachItem" => true,
        ),
        ">numX%sumZ" => array(
            "module" => "moreSumLimit",
            "description" => "При покупке больше X любых единиц товара устанавливается скидка на все товары при условии, что сумма покупки не менее Z",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "sumZ",
                    "replacement" => "0{1,9}",
                ),
            ),
            "discountEachItem" => true,
        ),
        ">numX#sumZ" => array(
            "module" => "moreSumItemLimit",
            "description" => "При покупке больше X любых единиц товара устанавливается скидка на все товары при условии, что цена каждого товара не менее Z",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "sumZ",
                    "replacement" => "0{1,9}",
                ),
            ),
            "discountEachItem" => true,
        ),
        ">%num" => array(
            "module" => "moreSimilar",
            "description" => "При покупке больше X одинаковых товаров устанавливается скидка на все товары из этого списка",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            ),
        ),
        ">%num#" => array(
            "module" => "moreSimilarNext",
            "description" => "При покупке больше Х одинаковых товаров устанавливается скидка на последующие товары из списка",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            )
        ),
        "=num" => array(
            "module" => "equal",
            "description" => "При покупке Х любых товаров устанавливается общая скидка",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            ),
            "discountEachItem" => true,
        ),
        "=%num" => array(
            "module" => "equalSimilar",
            "description" => "При покупке Х одинаковых товаров устанавливается скидка на эти товары",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            ),
            "discountEachItem" => true,
        ),
        "%num" => array(
            "module" => "everySimilar",
            "description" => "Скидка  на каждый i-й одинаковый товар",
            "regexp" => array(
                array(
                    "pattern" => "num",
                    "replacement" => "0{1,5}",
                ),
            )
        ),
        "numX%numY" => array(
            "module" => "dependSimilar",
            "description" => "При покупке X одинаковых товаров устанавливается скидка на Y товаров из этого списка",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "numY",
                    "replacement" => "0{1,5}",
                ),
            ),
            "discountEachItem" => true,
        ),
        "numX#numY" => array(
            "module" => "dependNonSimilar",
            "description" => "При покупке X любых товаров устанавливается скидка на 1шт Y единиц товаров самой низкой цены из этого списка",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "numY",
                    "replacement" => "0{1,5}",
                ),
            )
        ),
        "numX#numY#" => array(
            "module" => "dependNonSimilarSingle",
            "description" => "При покупке X любых товаров устанавливается скидка на Y товаров самой низкой цены из этого списка",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "numY",
                    "replacement" => "0{1,5}",
                ),
            )
        ),
        "numX#numY#sumZ" => array(
            "module" => "dependNonSimilarLimit",
            "description" => "При покупке X любых товаров устанавливается скидка на 1шт Y единиц товаров самой низкой цены из этого списка. 
                              Скидка начинает работать при цене одного из товаров не ниже Z",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "numY",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "sumZ",
                    "replacement" => "0{1,6}",
                ),
            )
        ),
        "numX#numY#sumZ#" => array(
            "module" => "dependNonSimilarLimitSingle",
            "description" => "При покупке X любых товаров устанавливается скидка на Y товаров самой низкой цены из этого списка. 
                              Скидка начинает работать при цене одного из товаров не ниже Z",
            "regexp" => array(
                array(
                    "pattern" => "numX",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "numY",
                    "replacement" => "0{1,5}",
                ),
                array(
                    "pattern" => "sumZ",
                    "replacement" => "0{1,6}",
                ),
            )
        ),
        "=" => array(
            "module" => "all",
            "description" => "Скидка на всю категорию или тип товара, вне зависимости от количества купленных товаров",
            "regexp" => array(
                array(
                    "pattern" => "",
                    "replacement" => "",
                ),
            ),
            "discountEachItem" => true,
        ),
        "-" => array(
            "module" => "deny",
            "description" => "Запрет на применение скидок для конкретной категории или типа товаров",
            "regexp" => array(
                array(
                    "pattern" => "",
                    "replacement" => "",
                ),
            )
        ),
        ">sum" => array(
            "module" => "sum",
            "description" => "При покупке товаров на сумму, большую чем X, устанавливается скидка на определенную категорию, определенный тип товаров",
            "regexp" => array(
                array(
                    "pattern" => "sum",
                    "replacement" => "0{1,9}",
                ),
            ),
            "discountEachItem" => true,
        ),
        ">%sum" => array(
            "module" => "sumSameAll",
            "description" => "При покупке в определенной категории определенный тип товаров на сумму, большую чем X, устанавливается общая скидка на весь заказ",
            "regexp" => array(
                array(
                    "pattern" => "sum",
                    "replacement" => "0{1,9}",
                ),
            )
        ),
        ">%sum#" => array(
            "module" => "sumSame",
            "description" => "При покупке в определенной категории определенный тип товаров на сумму, большую чем X, устанавливается скидка только на эту же категорию, этот же тип товара",
            "regexp" => array(
                array(
                    "pattern" => "sum",
                    "replacement" => "0{1,9}",
                ),
            )
        ),
        ">customerTotal" => array(
            "module" => "customerTotal",
            "description" => "Cкидка для конкретной категории или типа товаров по общей сумме всех заказов покупателя",
            "regexp" => array(
                array(
                    "pattern" => "customerTotal",
                    "replacement" => "0{1,9}",
                ),
            ),
            "discountEachItem" => true,
        ),
        ">=set" => array(
            "module" => "moreEqualSetAll",
            "description" => "При покупке более(или равно) X наборов (по одной уникальной единице) товаров из указанной категории начисляется скидка на весь заказ",
            "regexp" => array(
                array(
                    "pattern" => "set",
                    "replacement" => "0{1,3}",
                ),
            )
        ),
        ">=set#" => array(
            "module" => "moreEqualSet",
            "description" => "При покупке более(или равно) X наборов (по одной уникальной единице) товаров из указанной категории начисляется скидка на все товары указанной категории",
            "regexp" => array(
                array(
                    "pattern" => "set",
                    "replacement" => "0{1,3}",
                ),
            )
        ),
        ">=%set" => array(
            "module" => "moreEqualSetOnly",
            "description" => "При покупке более(или равно) X наборов (по одной уникальной единице) товаров из указанной категории начисляется скидка на все наборы указанной категории. Используйте товары с одинаковой ценой артикулов",
            "regexp" => array(
                array(
                    "pattern" => "set",
                    "replacement" => "0{1,3}",
                ),
            )
        ),
    ),
);
