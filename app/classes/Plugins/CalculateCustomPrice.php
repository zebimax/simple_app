<?php

namespace Plugins;


class CalculateCustomPrice
{
    /**
     * @param $productPrice
     * @param $productId
     * @param $danonewinkel
     * @param $cart
     * @param string $specialsNewProductPrice
     * @return array
     */
    public function __invoke($productPrice, $productId, $danonewinkel, $cart, $specialsNewProductPrice = 'x')
    {
        if ($danonewinkel) {

            // eerst controleren of het een speciaal "prijs" product is
            $artnr_up_q = tep_db_query("SELECT artnr_up, products_tax_class_id FROM products WHERE products_id = '" . tep_db_input($productId) . "'");
            if (tep_db_num_rows($artnr_up_q) > 0) {
                $artnr_up_r = tep_db_fetch_array($artnr_up_q);
                // we hebben het UP nummer, controleren of we daar een speciale prijs voor hebben
                $danone_up_prijzen_q = tep_db_query("SELECT price FROM danone_up_prijzen WHERE artnr_up = '" . $artnr_up_r['artnr_up'] . "'");
                if (tep_db_num_rows($danone_up_prijzen_q) > 0) {
                    $danone_up_prijzen_r = tep_db_fetch_array($danone_up_prijzen_q);
                    $tax = ($artnr_up_r['products_tax_class_id'] == 1) ? 1.21 : 1.06;

                    $productPrice = $danone_up_prijzen_r['price'] / $tax;

                    $return_array = array("products_price" => $productPrice,
                        "korting_dod" => $productPrice / 0.8 * 0.1 / $tax,
                        "korting_danone" => $productPrice / 0.8 * 0.1 / $tax,
                        "korting_niet_danone" => 0);
                    return $return_array;
                }
            }

            // merken uitsluiten
            $verboden_merken = array(793, // biobim
                460, // friso
                773, // Mead Johnson
                778, // Abbott
            );

            $merk_q = tep_db_query("SELECT products_id FROM products WHERE products_id = '" . $productId . "' AND manufacturers_id IN (" . join($verboden_merken, ",") . ")");
            if (tep_db_num_rows($merk_q) > 0) {
                $return_array = array("products_price" => $productPrice,
                    "korting_dod" => 0,
                    "korting_danone" => 0,
                    "korting_niet_danone" => 0);
                return $return_array;
            }

            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$productId . "' and status = '1' and DATE(NOW()) >= start_date AND DATE(NOW()) < expires_date");
            if (tep_db_num_rows ($specials_query)) {
                $specials = tep_db_fetch_array($specials_query);
                $productPrice = $specials['specials_new_products_price'];
            }


            $return_array = array("products_price" => $productPrice * 0.8,
                "korting_dod" => $productPrice * 0.1,
                "korting_danone" => 0,
                "korting_niet_danone" => $productPrice * 0.1);
            return $return_array;
        } else {

            // het is geen DANONE winkel; andere prijs acties die niet voor Danone gelden komen hier

            // actie voor Dieetpro; als het product
            // 21452, 21453, 21455, 21456
            // meer dan 2x in de winkelwagen zit; wordt de productprijs 49.90 / 2 / 1.19
            $dieet_pro_array = array(21452, 21453, 21455, 21456);
            if ($cart->get_quantity_pid_array($dieet_pro_array) >= 2) {
                if (in_array($productId, $dieet_pro_array)) {
                    return array("products_price" => (49.90 / 2 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }

            $coolbear_array = array(57243, 57241, 57244);
            if ($cart->get_quantity_pid_array($coolbear_array) >= 2) {
                if (in_array($productId, $coolbear_array)) {
                    return array("products_price" => (2.99 / 2 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }

            $sabai_array = array(48905, 6525);
            if ($cart->get_quantity_pid_array($sabai_array) >= 12) {
                if (in_array($productId, $sabai_array)) {
                    return array("products_price" => (6 / 1.21),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }

            $reasons_array = array(60577, 60578, 60579);
            if ($cart->get_quantity_pid_array($reasons_array) >= 2) {
                if (in_array($productId, $reasons_array)) {
                    return array("products_price" => (9.98 / 2 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }

            $nuslank_array = array(53827, 53828, 53829, 53832, 53833, 53834, 53836, 53837, 53838, 53840, 53841, 53842, 53843, 53844, 53845, 53846, 53847, 53848, 53849, 53850, 54077, 57116, 57115, 57114);
            $num_nuslank_found = $cart->get_quantity_pid_array($nuslank_array);

            if ($num_nuslank_found >= 20) {
                if (in_array($productId, $nuslank_array)) {
                    return array("products_price" => (30 / 20 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            } else if ($num_nuslank_found >= 6) {
                if (in_array($productId, $nuslank_array)) {
                    return array("products_price" => (10 / 6 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }


            // kijken of de gardeno of live actie loopt
            if (isset($_SESSION['gol_actie']) && $_SESSION['gol_actie'] == true) {
                $gol_array = array(21436, 21434, 21435, 21438, 21440, 21441, 21437, 21439);
                if (in_array($productId, $gol_array)) {
                    return array("products_price" => $productPrice - (3 / 1.06),
                        "korting_dod" => 0,
                        "korting_danone" => 0,
                        "korting_niet_danone" => 0);

                }
            }

            // kijken of de bloem actie loopt
            if (BLOEM_ACTIE == 'true') {
                $is_bloem_product = tep_db_query("SELECT products_id FROM products WHERE products_id = '" . (int)$productId . "' AND manufacturers_id = '35'");
                if (tep_db_num_rows($is_bloem_product)) {
                    $total_bloem_products_in_cart = $cart->calculate_total_num_products_manufacturer(35);
                    if ($total_bloem_products_in_cart) {
                        if ($total_bloem_products_in_cart >= 1)
                            $korting_bloem = 0.90;
                        if ($total_bloem_products_in_cart >= 2)
                            $korting_bloem = 0.85;
                        if ($total_bloem_products_in_cart >= 3)
                            $korting_bloem = 0.8;

                        return array("products_price" => $productPrice * $korting_bloem,
                            "korting_dod" => 0,
                            "korting_danone" => 0,
                            "korting_niet_danone" => 0);
                    }
                }
            }

            // kijken of het een special is
            $specials = array();
            if ($specialsNewProductPrice == 'x') {
                $specials_query = tep_db_query("select specials_new_products_price, expires_date from " . TABLE_SPECIALS . " where products_id = '" . (int)$productId . "' and status = '1' AND DATE(NOW()) >= start_date AND DATE(NOW()) < expires_date");
                if (tep_db_num_rows($specials_query))
                    $specials = tep_db_fetch_array($specials_query);
            } else {
                if ($specialsNewProductPrice != "")
                    $specials['specials_new_products_price'] = $specialsNewProductPrice;
            }

            if (array_key_exists("specials_new_products_price", $specials)) {

                $update_price_to_special_price = true;
                // uitzondering maken voor per 3 bestelbaar
                if ($max_afname = '')
                    $max_afname_r = tep_db_fetch_array(tep_db_query("SELECT max_afname FROM products WHERE products_id = '" . tep_db_input(tep_get_prid($productId)) . "'"));
                else
                    $max_afname_r['max_afname'] = $max_afname;

                if ($update_price_to_special_price) {
                    $productPrice = $specials['specials_new_products_price'];
                } else {
                    $products_query = tep_db_query("select p.products_price from " . TABLE_PRODUCTS . " p WHERE p.products_id = '" . (int)$productId . "'");
                    if ($products = tep_db_fetch_array($products_query)) {
                        $productPrice = $products['products_price'];
                    }
                }
            }

            $result = array(
                "products_price" => $productPrice,
                "korting_dod" => 0,
                "korting_danone" => 0,
                "korting_niet_danone" => 0
            );

            if (isset($specials['expires_date'])) {
                $result['expires_special_date'] = $specials['expires_date'];
            }

            return $result;
        }
    }
}