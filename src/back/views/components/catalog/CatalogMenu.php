<?php
include_once AuWebRoot.'/src/back/import/db.php';

class CatalogMenu {

    public function renderMenu() {
        $goods = new DBGoodsType();
        $goods->executeRequest(DB::TABLE_NAV_KEY___NAME, '', '', DB::TABLE_NAV_KEY__VALUE);
        echo "<div>";
        while ($row = mysqli_fetch_array($goods->getResponse())) {
            self::renderMenuItem($row['key'], $row['value']);
        }
        echo "</div>";
}

public function renderMenuItem($itemKey, $itemValue) {
    echo "<div id='nav_item_horizontal' class='left_menu_item font_arial float_left text_non_select border-round-6px ' unselectable='on' onclick=\"window.location='?page_name=catalog&key=".$itemKey."'\">".$itemValue."</div>";
}

}
