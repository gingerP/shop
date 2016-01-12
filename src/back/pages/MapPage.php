<?php

class Map {

    public function Map() {
    }

    public function render() {
        echo "<div class='map_page float_left'>";
        self::renderOfficeMap();
        self::renderSouthMarketMap();
        self::renderSkidelMarketMap();
        echo "</div>";
        echo "
            <script src='https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false'></script>
            <script src='scripts/js/maps.js'></script>
            ";

    }

    private function renderOfficeMap() {
        self::renderOfficeDescription();
        echo "<div class='map_window'>
                    <!--<div class='map_cap text_right cursor_pointer font_arial' onclick=\"window.open('https://maps.google.com/maps?f=d&amp;source=embed&amp;saddr=%D1%83%D0%BB.+%D0%9B%D0%B8%D0%B7%D1%8B+%D0%A7%D0%B0%D0%B9%D0%BA%D0%B8%D0%BD%D0%BE%D0%B9&amp;daddr=&amp;hl=ru&amp;geocode=FbXWMgMdqBhrAQ&amp;sll=53.663065,23.79702&amp;sspn=0.004081,0.013078&amp;t=h&amp;mra=mr&amp;ie=UTF8&amp;ll=53.663071,23.797159&amp;spn=0.004081,0.013078')\">
                        Просмотреть увеличенную карту
                    </div>-->
                    <div id='office' class='float_left'></div>
                </div>";
    }

    private function renderSouthMarketMap() {
        echo "<div class=\"map_info_second green_text font_arial\">
                    <div class='market_adress' >Наша точка на рынке Южный: ряд Радужный, место 87</div>
                <ul class='map_market_items'>
                    <li>Спец. одежда</li>
                    <li>Спец. обувь</li>
                    <li>Постельные принадлежности</li>
                </ul>
                </div>";

        echo "<div class='map_window'>
                    <!--<div class=\"map_cap text_right cursor_pointer font_arial\" onclick=\"window.open('https://maps.google.com/maps/ms?msa=0&amp;msid=207841827641737770061.0004da7668cf793b11e39&amp;ie=UTF8&amp;ll=53.621348,23.867637&amp;spn=0,0&amp;t=h&amp;source=embed')\">
                        Просмотреть увеличенную карту
                    </div>-->
                    <div id='southMarket' class='float_left'></div>
                </div>";
    }

    private function renderSkidelMarketMap() {
        echo "<div class=\"map_info_second green_text font_arial\">
                    <div class='market_adress' >Наша точка на рынке Южный: ряд Радужный, место 87</div>
                <ul class='map_market_items'>
                    <li>Спец. одежда</li>
                    <li>Спец. обувь</li>
                    <li>Постельные принадлежности</li>
                </ul>
                </div>";

        echo "<div class='map_window'>
                    <!--<div class=\"map_cap text_right cursor_pointer font_arial\" onclick=\"window.open('https://maps.google.com/maps/ms?msa=0&amp;msid=207841827641737770061.0004da7668cf793b11e39&amp;ie=UTF8&amp;ll=53.621348,23.867637&amp;spn=0,0&amp;t=h&amp;source=embed')\">
                        Просмотреть увеличенную карту
                    </div>-->
                    <div id='skidelMarket' class='float_left'></div>
                </div>";

    }

    private function renderOfficeDescription() {
        echo "<div class='map_info_first green_text font_arial'>
                <div style='margin-left: 6px;'>Наш адрес: г.Гродно, ул. Л.Чайкиной, 4</div>
                <div class='phone_cell float_left'>
                    <div class='phone_ico float_left'></div>
                    <div class='float_left phone_number'>тел./факс 8 (0152) 53-02-49</div>
                </div>
                <div class='phone_cell float_left'>
                    <div class='mts_phone float_left'></div>
                    <div class='float_left phone_number'>МТС +375 (29) 266-36-83</div>
                </div>
                <div class='phone_cell float_left'>
                    <div class='velcom_phone float_left'></div>
                    <div class='float_left phone_number'>Velcom +375 (29) 121-88-30</div>
                </div>
            </div>
          ";
    }


}
