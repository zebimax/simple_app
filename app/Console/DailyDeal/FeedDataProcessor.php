<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 03.03.15
 * Time: 16:51
 */

namespace Console\DailyDeal;


use Feeds\AbstractFeedDataProcessor;

class FeedDataProcessor extends AbstractFeedDataProcessor
{
    protected function process()
    {
        $this->processStock()
            ->processImages()
            ->processDescription()
            ->processOriginalPrice()
            ->processOfferPrice()
            ->processDeepLink()
            ->processProductCategories()
            ->processDeliveryCosts();
    }

    /**
     * @return $this
     */
    protected function processProductCategories()
    {
        $categoryName = tep_get_category($this->feed->getDataValue('product_id'));
        $this->feed->setDataValue('all_categories', join('/', $categoryName));
        $categoryNames = array_reverse($categoryName, true);
        $this->feed->setDataValue('category', array_shift($categoryNames));
        $this->feed->setDataValue('sub_category', array_shift($categoryNames));
        return $this;
    }

    /**
     * @return $this
     */
    protected function processDeepLink()
    {
        $this->feed->setDataValue('deep_link', getDeepLink(
                array(
                    'products_id' => $this->feed->getDataValue('product_id'),
                    'cat' => $this->feed->getDataValue('cat')
                ),
                $this->feed->getId()
            )
        );
        return $this;
    }

    /**
     * @return $this
     */
    protected function processOfferPrice()
    {
        $offerPrice = number_format($this->feed->getDataValue('specials_new_products_price'), 2, '.', '');
        $this->feed->setDataValue('offer_price', $offerPrice);
        return $this;
    }

    /**
     * @return $this
     */
    protected function processOriginalPrice()
    {
        $originalPrice = number_format($this->feed->getDataValue('original_price'), 2, '.', '' );
        $this->feed->setDataValue('original_price', $originalPrice);
        return $this;
    }

    /**
     * @return $this
     */
    protected function processDescription()
    {
        $stripped = strip_tags($this->feed->getDataValue('description'));
        $value = (strcasecmp(substr($stripped, 0, 8), $this->getConfig('description_stripped')) == 0)
            ? substr($stripped, 8)
            : $stripped;

        $this->feed->setDataValue('description', $value);
        return $this;
    }

    /**
     * @return $this
     */
    protected function processImages()
    {
        $small = $full = '';
        $httpServer = $this->getConfig('http_server');
        $productImage = $this->feed->getDataValue('image');
        if (
            file_exists('images/' . $productImage) &&
            in_array(
                strtolower(pathinfo($productImage, PATHINFO_EXTENSION)),
                $this->getConfig('image_allowed_extensions', array())
            )
        ) {
            $imagesDir = $this->getConfig('images_dir');
            $small = $httpServer . '/' . tep_image_name_small(
                $imagesDir . $productImage,
                    $this->getConfig('small_image_width'),
                    $this->getConfig('small_image_height')
                );
            $full = $httpServer . '/' . tep_image_name($imagesDir . $productImage);
        }
        $this->feed->setDataValue('image_small', $small);
        $this->feed->setDataValue('image_full', $full);

        return $this;
    }

    /**
     * @return $this
     */
    protected function processDeliveryCosts()
    {
        $deliveryCosts = '3.95';
        if ($this->feed->getDataValue('offer_price') > 19.99) {
            $deliveryCosts = 0;
        }
        $this->feed->setDataValue('delivery_costs', $deliveryCosts);
        return $this;
    }

    /**
     * @return $this
     */
    protected function processStock()
    {
        $quantity = $this->feed->getDataValue('quantity');
        if ($quantity == 0) {
            $stock = 0;
        } elseif ($quantity == 1) {
            $stock = 1;
        } elseif ($quantity < 5) {
            $stock = 5;
        } elseif ($quantity < 10) {
            $stock = 10;
        } elseif ($quantity < 50) {
            $stock = 50;
        } else {
            $stock = 100;
        }
        $this->feed->setDataValue('stock', $stock);
        return $this;
    }
}