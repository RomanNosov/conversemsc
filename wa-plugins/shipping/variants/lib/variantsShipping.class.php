<?php

/**
 * @property string $test_mode
 * @property string $api_key
 * @property string $zip
 * @property string $length
 * @property string $height
 * @property string $width
 */
class variantsShipping extends waShipping {

    /**
     * @var string
     */
    private $currency = 'RUB';

    /**
     *
     * @return string ISO3 currency code or array of ISO3 codes
     */
    public function allowedCurrency() {
        return $this->currency;
    }

    /**
     *
     * @return string Weight units or array of weight units
     */
    public function allowedWeightUnit() {
        return 'kg';
    }

    /**
     * @return array|string
     */
    protected function calculate() {

        $user_ip = getenv('REMOTE_ADDR');
        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
        
        $total = $this->getTotalPrice();
        $delivery = $total <= 2500;
        $user = strpos($_SERVER["REQUEST_URI"], "webasyst") === false;

        $cl = isset($_GET["cl"]) ? "cl" : "";

        $msc1 = "msc1$cl";
        $msc2 = "msc2$cl";
        $msc2km = "msc2km$cl";
        $msc3 = "msc3$cl";
        $spb1 = "spb1$cl";
        $spb2 = "spb2$cl";
        $post = "post$cl";
        $regcour = "regcour$cl";
        $regpick = "regpick$cl";

        $del = array(
            'msc1' => array(
                'name' => "Москва - курьер, в пределах МКАД", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$msc1 . " рублей" : $this->$msc1 . " рублей"),
                'rate' => ($delivery ? $this->$msc1 . " рублей" : $this->$msc1 . " рублей"), //точная стоимость доставки
            ),
            'msc2' => array(
                'name' => "Москва - курьер, за МКАД", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$msc2 . ' рублей + ' . $this->$msc2km . ' рублей/км' : $this->$msc2 . ' рублей + ' . $this->$msc2km . ' рублей/км'),
                'rate' => ($delivery ? $this->$msc2 . ' рублей + ' . $this->$msc2km . ' рублей/км' : $this->$msc2 . ' рублей + ' . $this->$msc2km . ' рублей/км'), //точная стоимость доставки
            ),
            'msc3' => array(
                'name' => "Москва - самовывоз", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$msc3 . ' рублей' : $this->$msc3 . ' рублей'),
                'rate' => ($delivery ? $this->$msc3 . ' рублей' : $this->$msc3 . ' рублей'), //точная стоимость доставки
            ),
            'spb1' => array(
                'name' => "СПб - курьер", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$spb1 . ' рублей' : $this->$spb1 . ' рублей'),
                'rate' => ($delivery ? $this->$spb1 . ' рублей' : $this->$spb1 . ' рублей'), //точная стоимость доставки
            ),
            'spb2' => array(
                'name' => "СПб - самовывоз", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$spb2 . ' рублей' : $this->$spb2 . ' рублей'),
                'rate' => ($delivery ? $this->$spb2 . ' рублей' : $this->$spb2 . ' рублей'), //точная стоимость доставки
            ),
            'post' => array(
                'name' => "Почта", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$post . ' рублей' : $this->$post . ' рублей'),
                'rate' => ($delivery ? $this->$post . ' рублей' : $this->$post . ' рублей'), //точная стоимость доставки
            ),
            'regcour' => array(
                'name' => "Регионы - курьер", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$regcour . ' рублей' : $this->$regcour . ' рублей'),
                'rate' => ($delivery ? $this->$regcour . ' рублей' : $this->$regcour . ' рублей'), //точная стоимость доставки
            ),
            'regpick' => array(
                'name' => "Регионы - самовывоз", //название варианта доставки, например, “Наземный  транспорт”, “Авиа”, “Express Mail” и т. д.
                'description' => '', //необязательное описание варианта  доставки
                'est_delivery' => '', //произвольная строка, содержащая  информацию о примерном времени доставки
                'currency' => $this->currency, //ISO3-код валюты, в которой рассчитана  стоимость  доставки
                'rate_orig' => ($delivery ? $this->$regpick . ' рублей' : $this->$regpick . ' рублей'),
                'rate' => ($delivery ? $this->$regpick . ' рублей' : $this->$regpick . ' рублей'), //точная стоимость доставки
            )
        );
        
        return $del;
    }

}
