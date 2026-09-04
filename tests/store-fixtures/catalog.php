<?php

namespace Tests\StoreFixtures;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover StudioCart shapes for the local/test store seed. Not plugin code.
 */
class Catalog
{
    public const PRODUCT_POST_TYPE      = 'sc_product';
    public const ORDER_POST_TYPE        = 'sc_order';
    public const SUBSCRIPTION_POST_TYPE = 'sc_subscription';
    public const PRODUCT_CAT_TAXONOMY   = 'sc_product_cat';
    public const PRODUCT_TAG_TAXONOMY   = 'sc_product_tag';
    public const CHECKOUT_SHORTCODE     = 'studiocart-form';
    public const STORE_SHORTCODE        = 'studiocart-store';
    public const ACCOUNT_SHORTCODE      = 'studiocart_account';
    public const RECEIPT_SHORTCODE      = 'studiocart-receipt';
    public const CUSTOMER_PASSWORD      = 'studiocart';
    public const CUSTOMER_LOGIN_PREFIX  = 'sc_seed_customer_';
    public const COUPON_CODE            = 'SEED100';
    public const COUPON_LAUNCH          = 'LAUNCH10';
    public const COUPON_SAVE            = 'SAVE15';
    public const DEFAULT_PRODUCTS       = 14;
    public const DEFAULT_CUSTOMERS      = 40;
    public const DEFAULT_ORDERS         = 96;
    public const DEFAULT_SUBSCRIPTIONS  = 20;
    public const MONTH_DAYS             = 30;

    /**
     * @return array<string, string>
     */
    public static function leftover_store_options()
    {
        return [
            '_sc_currency'                 => 'USD',
            '_sc_currency_position'        => 'left',
            '_sc_cashondelivery_enable'    => '1',
            '_sc_stripe_enable'            => '',
            '_sc_paypal_enable'            => '',
            '_sc_terms_url'                => 'https://example.test/terms',
            '_sc_email_confirmation_body'  => 'Thanks for your order [studiocart-receipt]',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function product_common_meta()
    {
        return [
            '_sc_plan_heading'          => 'Payment Plan',
            '_sc_button_color'          => '#000000',
            '_sc_button_text'           => 'Order Now',
            '_sc_step1_button_label'    => 'Continue',
            '_sc_step1_button_icon_pos' => 'left',
            '_sc_cart_close_action'     => 'message',
            '_sc_cart_closed_message'   => 'Sorry, this product is no longer for sale.',
            '_sc_show_full_price'       => '1',
            '_sc_page_template'         => '',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function categories()
    {
        return [ 'Courses', 'Memberships', 'Workshops', 'Downloads' ];
    }

    /**
     * @return array<int, string>
     */
    public static function tags()
    {
        return [ 'Beginner', 'Pro', 'Sale', 'Evergreen', 'Cohort' ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function product_catalog()
    {
        return [
            [
                'key'      => 'intro',
                'title'    => 'Intro Course',
                'slug'     => 'seed-intro-course',
                'type'     => 'one-time',
                'category' => 'Courses',
                'tag'      => 'Beginner',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('intro_plan', 'One payment of $49', '49') ],
                ],
            ],
            [
                'key'      => 'membership',
                'title'    => 'Pro Membership',
                'slug'     => 'seed-pro-membership',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('membership_plan', 'Monthly membership', '29') ],
                ],
            ],
            [
                'key'      => 'coaching',
                'title'    => 'Monthly Coaching',
                'slug'     => 'seed-monthly-coaching',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Cohort',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('coaching_plan', 'Monthly coaching', '99', '6') ],
                ],
            ],
            [
                'key'      => 'workshop',
                'title'    => 'Free Workshop',
                'slug'     => 'seed-free-workshop',
                'type'     => 'free',
                'category' => 'Workshops',
                'tag'      => 'Beginner',
                'meta'     => [
                    '_sc_pay_options' => [ self::free_plan('workshop_plan', 'Free access') ],
                ],
            ],
            [
                'key'      => 'coupon',
                'title'    => 'Starter Kit',
                'slug'     => 'seed-starter-kit',
                'type'     => 'one-time',
                'category' => 'Downloads',
                'tag'      => 'Beginner',
                'meta'     => [
                    '_sc_pay_options'       => [ self::one_time_plan('starter_plan', 'One payment of $19', '19') ],
                    '_sc_show_coupon_field' => '1',
                    '_sc_coupons'           => [
                        self::coupon(self::COUPON_CODE, 'cart-percent', '100'),
                        self::coupon(self::COUPON_LAUNCH, 'cart-percent', '10'),
                        self::coupon(self::COUPON_SAVE, 'cart-fixed', '15'),
                    ],
                ],
            ],
            [
                'key'      => 'taxable',
                'title'    => 'Taxable Course',
                'slug'     => 'seed-taxable-course',
                'type'     => 'one-time',
                'category' => 'Courses',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options'         => [ self::one_time_plan('taxable_plan', 'One payment of $79', '79') ],
                    '_sc_product_taxable'     => 'tax',
                    '_sc_show_address_fields' => '1',
                ],
            ],
            [
                'key'      => 'bump-offer',
                'title'    => 'Bonus PDF',
                'slug'     => 'seed-bonus-pdf',
                'type'     => 'one-time',
                'category' => 'Downloads',
                'tag'      => 'Evergreen',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('bump_plan', 'Bump offer $5', '5') ],
                ],
            ],
            [
                'key'      => 'bump-main',
                'title'    => 'Launch Blueprint',
                'slug'     => 'seed-launch-blueprint',
                'type'     => 'one-time',
                'category' => 'Courses',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('blueprint_plan', 'One payment of $149', '149') ],
                    '_sc_order_bump'  => '1',
                ],
            ],
            [
                'key'      => 'annual',
                'title'    => 'Annual Pass',
                'slug'     => 'seed-annual-pass',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Evergreen',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('annual_plan', 'Billed yearly', '199', '-1', 'year') ],
                ],
            ],
            [
                'key'      => 'sale',
                'title'    => 'Launch Sale Workshop',
                'slug'     => 'seed-launch-sale',
                'type'     => 'one-time',
                'category' => 'Workshops',
                'tag'      => 'Sale',
                'meta'     => [
                    '_sc_pay_options' => [ self::sale_plan('sale_plan', 'Workshop seat', '99', '69') ],
                ],
            ],
            [
                'key'      => 'team',
                'title'    => 'Team License',
                'slug'     => 'seed-team-license',
                'type'     => 'one-time',
                'category' => 'Downloads',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [
                        self::one_time_plan('team_once', 'One payment of $249', '249'),
                        self::recurring_plan('team_monthly', 'Team monthly', '29'),
                    ],
                ],
            ],
            [
                'key'      => 'pwyw',
                'title'    => 'Office Hours',
                'slug'     => 'seed-office-hours',
                'type'     => 'pwyw',
                'category' => 'Workshops',
                'tag'      => 'Evergreen',
                'meta'     => [
                    '_sc_pay_options' => [ self::pwyw_plan('pwyw_plan', 'Pay what you want', '25') ],
                ],
            ],
            [
                'key'      => 'closed',
                'title'    => 'Closed Cohort',
                'slug'     => 'seed-closed-cohort',
                'type'     => 'one-time',
                'category' => 'Courses',
                'tag'      => 'Cohort',
                'meta'     => [
                    '_sc_pay_options'      => [ self::one_time_plan('closed_plan', 'Cohort seat $399', '399') ],
                    '_sc_hide_product_page' => '1',
                ],
            ],
            [
                'key'      => 'terms',
                'title'    => 'Terms Masterclass',
                'slug'     => 'seed-terms-masterclass',
                'type'     => 'one-time',
                'category' => 'Courses',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('terms_plan', 'One payment of $129', '129') ],
                ],
            ],
        ];
    }

    /**
     * @param int $count Count.
     * @return array<int, array<string, mixed>>
     */
    public static function product_catalog_for_count($count)
    {
        $catalog  = self::product_catalog();
        $count    = max(1, (int) $count);
        $selected = array_slice($catalog, 0, min($count, count($catalog)));
        $cats     = self::categories();
        $tags     = self::tags();

        for ($i = count($selected); $i < $count; $i++) {
            $n          = $i + 1;
            $recurring  = (0 === $i % 2);
            $selected[] = [
                'key'      => 'offer-' . $n,
                'title'    => 'Offer ' . $n,
                'slug'     => 'seed-offer-' . $n,
                'type'     => $recurring ? 'recurring' : 'one-time',
                'category' => $cats[ $i % count($cats) ],
                'tag'      => $tags[ $i % count($tags) ],
                'meta'     => [
                    '_sc_pay_options' => [
                        $recurring
                            ? self::recurring_plan('offer_' . $n, 'Monthly offer ' . $n, (string) (19 + $n))
                            : self::one_time_plan('offer_' . $n, 'One payment', (string) (19 + $n)),
                    ],
                ],
            ];
        }

        return $selected;
    }

    /**
     * @param int $product_id Product ID.
     * @return string
     */
    public static function checkout_shortcode($product_id)
    {
        return sprintf('[%1$s id="%2$d"]', self::CHECKOUT_SHORTCODE, (int) $product_id);
    }

    /**
     * Spread an index across the last 30 days (promo cluster in week 2).
     *
     * @param int $index Zero-based.
     * @param int $total Total items.
     * @return string MySQL datetime.
     */
    public static function month_post_date($index, $total)
    {
        $total = max(1, (int) $total);
        $index = max(0, (int) $index);
        $slot  = $total <= 1 ? 0.5 : $index / ($total - 1);
        if ($slot > 0.28 && $slot < 0.52) {
            $slot = 0.32 + ( ( $slot - 0.28 ) / 0.24 ) * 0.12;
        }
        $days_ago = (int) round(( 1 - $slot ) * ( self::MONTH_DAYS - 1 ));
        $seconds  = ( $index * 173 ) % 72000;

        return gmdate('Y-m-d H:i:s', time() - ( $days_ago * 86400 ) - $seconds);
    }

    /**
     * @param int $count Count.
     * @return array<int, array<string, string>>
     */
    public static function customer_profiles_for_count($count)
    {
        $base  = self::customer_profiles();
        $count = max(1, (int) $count);
        if ($count <= count($base)) {
            return array_slice($base, 0, $count);
        }

        $first = [ 'Alex', 'Jordan', 'Taylor', 'Casey', 'Riley', 'Morgan', 'Avery', 'Quinn', 'Jamie', 'Drew', 'Skyler', 'Reese', 'Cameron', 'Hayden', 'Rowan', 'Parker' ];
        $last  = [ 'Rivera', 'Kim', 'Brooks', 'Nguyen', 'Patel', 'Chen', 'Johnson', 'Martinez', 'Wilson', 'Thompson', 'Lopez', 'Adams', 'Singh', 'Garcia', 'Khan', 'Okafor' ];
        $out   = $base;
        for ($i = count($base); $i < $count; $i++) {
            $out[] = [
                'first_name' => $first[ $i % count($first) ],
                'last_name'  => $last[ (int) floor($i / count($first)) % count($last) ] . ' ' . ( $i + 1 ),
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function customer_profiles()
    {
        return [
            [ 'first_name' => 'Alex', 'last_name' => 'Rivera' ],
            [ 'first_name' => 'Jordan', 'last_name' => 'Kim' ],
            [ 'first_name' => 'Taylor', 'last_name' => 'Brooks' ],
            [ 'first_name' => 'Casey', 'last_name' => 'Nguyen' ],
            [ 'first_name' => 'Riley', 'last_name' => 'Patel' ],
            [ 'first_name' => 'Morgan', 'last_name' => 'Chen' ],
            [ 'first_name' => 'Avery', 'last_name' => 'Johnson' ],
            [ 'first_name' => 'Quinn', 'last_name' => 'Martinez' ],
            [ 'first_name' => 'Jamie', 'last_name' => 'Wilson' ],
            [ 'first_name' => 'Drew', 'last_name' => 'Thompson' ],
            [ 'first_name' => 'Skyler', 'last_name' => 'Lopez' ],
            [ 'first_name' => 'Reese', 'last_name' => 'Adams' ],
        ];
    }

    /**
     * @param string $option_id   Plan id.
     * @param string $option_name Label.
     * @param string $price       Price.
     * @return array<string, string>
     */
    public static function one_time_plan($option_id, $option_name, $price)
    {
        return [
            'option_id'         => $option_id,
            'option_name'       => $option_name,
            'url_slug'          => $option_id,
            'product_type'      => '',
            'price'             => $price,
            'frequency'         => '1',
            'sale_frequency'    => '1',
            'interval'          => 'day',
            'sale_interval'     => 'day',
            'installments'      => '-1',
            'sale_installments' => '-1',
            'stripe_plan_id'    => $option_id,
        ];
    }

    /**
     * @param string $option_id    Plan id.
     * @param string $option_name  Label.
     * @param string $price        Price.
     * @param string $installments Installments.
     * @param string $interval     Interval.
     * @return array<string, string>
     */
    public static function recurring_plan($option_id, $option_name, $price, $installments = '-1', $interval = 'month')
    {
        $plan                      = self::one_time_plan($option_id, $option_name, $price);
        $plan['product_type']      = 'recurring';
        $plan['interval']          = $interval;
        $plan['sale_interval']     = $interval;
        $plan['installments']      = $installments;
        $plan['sale_installments'] = $installments;

        return $plan;
    }

    /**
     * @param string $option_id   Plan id.
     * @param string $option_name Label.
     * @param string $price       List price.
     * @param string $sale_price  Sale price.
     * @return array<string, string>
     */
    public static function sale_plan($option_id, $option_name, $price, $sale_price)
    {
        $plan                      = self::one_time_plan($option_id, $option_name, $price);
        $plan['sale_price']        = $sale_price;
        $plan['sale_option_name']  = $option_name . ' (sale)';
        $plan['sale_stripe_plan_id'] = $option_id . '_sale';

        return $plan;
    }

    /**
     * @param string $option_id   Plan id.
     * @param string $option_name Label.
     * @param string $price       Suggested price.
     * @return array<string, string>
     */
    public static function pwyw_plan($option_id, $option_name, $price)
    {
        $plan                 = self::one_time_plan($option_id, $option_name, $price);
        $plan['product_type'] = 'pwyw';

        return $plan;
    }

    /**
     * @param string $option_id   Plan id.
     * @param string $option_name Label.
     * @return array<string, string>
     */
    public static function free_plan($option_id, $option_name)
    {
        $plan                 = self::one_time_plan($option_id, $option_name, '0');
        $plan['product_type'] = 'free';

        return $plan;
    }

    /**
     * @param string $code   Code.
     * @param string $type   Type.
     * @param string $amount Amount.
     * @return array<string, string>
     */
    public static function coupon($code, $type, $amount)
    {
        return [
            'code'      => $code,
            'type'      => $type,
            'amount'    => $amount,
            'limit'     => '',
            'expires'   => '',
            'stripe_id' => 'seed_' . strtolower($code),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function leftover_meta_key_list()
    {
        $keys   = array_keys(self::product_common_meta());
        $keys[] = '_sc_pay_options';
        $keys[] = '_sc_product_id';
        $keys[] = '_sc_product_name';
        $keys[] = '_sc_item_name';
        $keys[] = '_sc_option_id';
        $keys[] = '_sc_plan';
        $keys[] = '_sc_amount';
        $keys[] = '_sc_email';
        $keys[] = '_sc_first_name';
        $keys[] = '_sc_last_name';
        $keys[] = '_sc_pay_method';
        $keys[] = '_sc_user_account';
        $keys[] = '_sc_status';
        $keys[] = '_sc_sub_status';
        $keys[] = '_sc_subscription_id';
        $keys[] = '_sc_customer_id';
        $keys[] = '_sc_coupons';
        $keys[] = '_sc_coupon';
        $keys[] = '_sc_order_bump';
        $keys[] = '_sc_notifications';
        $keys[] = '_sc_refund_log';
        $keys[] = '_sc_tax_amount';
        $keys[] = '_sc_quantity';
        $keys[] = '_sc_renewal_order';

        return array_values(array_unique($keys));
    }
}
