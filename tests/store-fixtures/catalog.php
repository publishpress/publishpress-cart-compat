<?php

namespace Tests\StoreFixtures;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover StudioCart shapes for the local/test store seed. Not plugin code.
 *
 * Display copy is a fictitious WordPress-plugin shop (Northstar) so the
 * seeded admin/storefront looks like a merchant catalog, not "Offer 3".
 * Internal `key` values stay stable for the seeder (bumps, coupons, amounts).
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
        return [ 'Editorial', 'SEO', 'Commerce', 'Memberships' ];
    }

    /**
     * @return array<int, string>
     */
    public static function tags()
    {
        return [ 'Lite', 'Pro', 'Sale', 'Gutenberg', 'WooCommerce' ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function product_catalog()
    {
        return [
            [
                'key'      => 'intro',
                'title'    => 'Quill — Editorial Calendar',
                'slug'     => 'seed-quill-editorial-calendar',
                'type'     => 'one-time',
                'category' => 'Editorial',
                'tag'      => 'Gutenberg',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('intro_plan', 'Single-site license — $49', '49') ],
                ],
            ],
            [
                'key'      => 'membership',
                'title'    => 'Quill Club',
                'slug'     => 'seed-quill-club',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('membership_plan', 'Monthly club membership — $29', '29') ],
                ],
            ],
            [
                'key'      => 'coaching',
                'title'    => 'Willow Support Desk',
                'slug'     => 'seed-willow-support-desk',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('coaching_plan', 'Monthly support — 6 months', '99', '6') ],
                ],
            ],
            [
                'key'      => 'workshop',
                'title'    => 'Quill Lite',
                'slug'     => 'seed-quill-lite',
                'type'     => 'free',
                'category' => 'Editorial',
                'tag'      => 'Lite',
                'meta'     => [
                    '_sc_pay_options' => [ self::free_plan('workshop_plan', 'Free lite license') ],
                ],
            ],
            [
                'key'      => 'coupon',
                'title'    => 'Harbor Forms',
                'slug'     => 'seed-harbor-forms',
                'type'     => 'one-time',
                'category' => 'Commerce',
                'tag'      => 'Sale',
                'meta'     => [
                    '_sc_pay_options'       => [ self::one_time_plan('starter_plan', 'Single-site license — $19', '19') ],
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
                'title'    => 'Ledger Invoices',
                'slug'     => 'seed-ledger-invoices',
                'type'     => 'one-time',
                'category' => 'Commerce',
                'tag'      => 'WooCommerce',
                'meta'     => [
                    '_sc_pay_options'         => [ self::one_time_plan('taxable_plan', 'Single-site license — $79', '79') ],
                    '_sc_product_taxable'     => 'tax',
                    '_sc_show_address_fields' => '1',
                ],
            ],
            [
                'key'      => 'bump-offer',
                'title'    => 'Atlas Local Pack',
                'slug'     => 'seed-atlas-local-pack',
                'type'     => 'one-time',
                'category' => 'SEO',
                'tag'      => 'Gutenberg',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('bump_plan', 'Add-on license — $5', '5') ],
                ],
            ],
            [
                'key'      => 'bump-main',
                'title'    => 'Atlas SEO',
                'slug'     => 'seed-atlas-seo',
                'type'     => 'one-time',
                'category' => 'SEO',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('blueprint_plan', 'Single-site license — $149', '149') ],
                    '_sc_order_bump'  => '1',
                ],
            ],
            [
                'key'      => 'annual',
                'title'    => 'Atlas SEO Club',
                'slug'     => 'seed-atlas-seo-club',
                'type'     => 'recurring',
                'category' => 'Memberships',
                'tag'      => 'Gutenberg',
                'meta'     => [
                    '_sc_pay_options' => [ self::recurring_plan('annual_plan', 'Annual club membership — $199', '199', '-1', 'year') ],
                ],
            ],
            [
                'key'      => 'sale',
                'title'    => 'Compass Redirects',
                'slug'     => 'seed-compass-redirects',
                'type'     => 'one-time',
                'category' => 'SEO',
                'tag'      => 'Sale',
                'meta'     => [
                    '_sc_pay_options' => [ self::sale_plan('sale_plan', 'Single-site license', '99', '69') ],
                ],
            ],
            [
                'key'      => 'team',
                'title'    => 'Beacon Analytics',
                'slug'     => 'seed-beacon-analytics',
                'type'     => 'one-time',
                'category' => 'Commerce',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options' => [
                        self::one_time_plan('team_once', 'Agency license — $249', '249'),
                        self::recurring_plan('team_monthly', 'Monthly agency seat — $29', '29'),
                    ],
                ],
            ],
            [
                'key'      => 'pwyw',
                'title'    => 'Beacon Tip Jar',
                'slug'     => 'seed-beacon-tip-jar',
                'type'     => 'pwyw',
                'category' => 'Commerce',
                'tag'      => 'Lite',
                'meta'     => [
                    '_sc_pay_options' => [ self::pwyw_plan('pwyw_plan', 'Pay what you want', '25') ],
                ],
            ],
            [
                'key'      => 'closed',
                'title'    => 'Nightwatch Security',
                'slug'     => 'seed-nightwatch-security',
                'type'     => 'one-time',
                'category' => 'Memberships',
                'tag'      => 'Pro',
                'meta'     => [
                    '_sc_pay_options'      => [ self::one_time_plan('closed_plan', 'Invite-only license — $399', '399') ],
                    '_sc_hide_product_page' => '1',
                ],
            ],
            [
                'key'      => 'terms',
                'title'    => 'Signpost Legal Pages',
                'slug'     => 'seed-signpost-legal',
                'type'     => 'one-time',
                'category' => 'Editorial',
                'tag'      => 'Gutenberg',
                'meta'     => [
                    '_sc_pay_options' => [ self::one_time_plan('terms_plan', 'Single-site license — $129', '129') ],
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
        $extras   = self::extra_product_catalog();
        $editions = [ '', ' Pro', ' Agency', ' Cloud', ' Studio' ];

        for ($i = count($selected); $i < $count; $i++) {
            $n         = $i + 1;
            $extra_i   = $i - count($catalog);
            $base      = $extras[ $extra_i % count($extras) ];
            $edition   = (int) floor($extra_i / count($extras));
            $cycle     = (int) floor($edition / count($editions));
            $title     = $base['title'] . $editions[ $edition % count($editions) ];
            if ($cycle > 0) {
                $title .= ' ' . ( $cycle + 1 );
            }
            $recurring = 'recurring' === $base['type'];
            $price     = (string) ( (int) $base['price'] + ( $edition * 10 ) );
            $selected[] = [
                'key'      => 'extra-' . $n,
                'title'    => $title,
                'slug'     => 'seed-extra-' . $n,
                'type'     => $base['type'],
                'category' => $base['category'],
                'tag'      => $base['tag'],
                'meta'     => [
                    '_sc_pay_options' => [
                        $recurring
                            ? self::recurring_plan('extra_' . $n, 'Monthly license — $' . $price, $price)
                            : self::one_time_plan('extra_' . $n, 'Single-site license — $' . $price, $price),
                    ],
                ],
            ];
        }

        return $selected;
    }

    /**
     * Named overflow plugins when `--products` exceeds the core catalog.
     *
     * @return array<int, array<string, string>>
     */
    public static function extra_product_catalog()
    {
        return [
            [ 'title' => 'Maple Comments', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Gutenberg', 'price' => '29' ],
            [ 'title' => 'Pebble Breadcrumbs', 'type' => 'one-time', 'category' => 'SEO', 'tag' => 'Gutenberg', 'price' => '19' ],
            [ 'title' => 'Finch Social Proof', 'type' => 'one-time', 'category' => 'Commerce', 'tag' => 'WooCommerce', 'price' => '39' ],
            [ 'title' => 'Oak Schema', 'type' => 'one-time', 'category' => 'SEO', 'tag' => 'Pro', 'price' => '49' ],
            [ 'title' => 'Pine Cache', 'type' => 'recurring', 'category' => 'Commerce', 'tag' => 'Pro', 'price' => '15' ],
            [ 'title' => 'Moss SMTP', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Lite', 'price' => '19' ],
            [ 'title' => 'Cedar Backups', 'type' => 'recurring', 'category' => 'Commerce', 'tag' => 'Pro', 'price' => '25' ],
            [ 'title' => 'Drift Related Posts', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Gutenberg', 'price' => '29' ],
            [ 'title' => 'River Table of Contents', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Gutenberg', 'price' => '19' ],
            [ 'title' => 'Cinder Image SEO', 'type' => 'one-time', 'category' => 'SEO', 'tag' => 'Gutenberg', 'price' => '35' ],
            [ 'title' => 'Lark Popups', 'type' => 'one-time', 'category' => 'Commerce', 'tag' => 'Sale', 'price' => '45' ],
            [ 'title' => 'Heron Multilingual', 'type' => 'recurring', 'category' => 'Editorial', 'tag' => 'Pro', 'price' => '29' ],
            [ 'title' => 'Ibis Accessibility', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Gutenberg', 'price' => '59' ],
            [ 'title' => 'Tern Media Library', 'type' => 'one-time', 'category' => 'Editorial', 'tag' => 'Pro', 'price' => '79' ],
            [ 'title' => 'Vole A/B Tests', 'type' => 'recurring', 'category' => 'Commerce', 'tag' => 'Pro', 'price' => '49' ],
            [ 'title' => 'Bramble Webhooks', 'type' => 'recurring', 'category' => 'Commerce', 'tag' => 'Pro', 'price' => '19' ],
        ];
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
        $used  = [];
        foreach ($base as $profile) {
            $used[ $profile['first_name'] . '|' . $profile['last_name'] ] = true;
        }

        $out = $base;
        $i   = 0;
        $cap = count($first) * count($last);
        while (count($out) < $count) {
            if ($i >= $cap) {
                $out[] = self::customer_profile_with_address('Northstar', 'Buyer ' . count($out), count($out));
                continue;
            }
            $first_name = $first[ $i % count($first) ];
            $last_name  = $last[ (int) floor($i / count($first)) % count($last) ];
            ++$i;
            $pair = $first_name . '|' . $last_name;
            if (isset($used[ $pair ])) {
                continue;
            }
            $used[ $pair ] = true;
            $out[] = self::customer_profile_with_address($first_name, $last_name, count($out));
        }

        return $out;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function customer_profiles()
    {
        $names = [
            [ 'Alex', 'Rivera' ],
            [ 'Jordan', 'Kim' ],
            [ 'Taylor', 'Brooks' ],
            [ 'Casey', 'Nguyen' ],
            [ 'Riley', 'Patel' ],
            [ 'Morgan', 'Chen' ],
            [ 'Avery', 'Johnson' ],
            [ 'Quinn', 'Martinez' ],
            [ 'Jamie', 'Wilson' ],
            [ 'Drew', 'Thompson' ],
            [ 'Skyler', 'Lopez' ],
            [ 'Reese', 'Adams' ],
        ];

        $out = [];
        foreach ($names as $i => $pair) {
            $out[] = self::customer_profile_with_address($pair[0], $pair[1], $i);
        }

        return $out;
    }

    /**
     * Leftover StudioCart account-profile fields for one seeded customer.
     *
     * @param string $first_name First name.
     * @param string $last_name  Last name.
     * @param int    $index      Profile index (varies street / phone).
     * @return array<string, string>
     */
    public static function customer_profile_with_address($first_name, $last_name, $index)
    {
        $index   = max(0, (int) $index);
        $streets = [ 'Market St', 'Mission St', 'Howard St', 'Folsom St', 'Bryant St', 'Brannan St' ];
        $cities  = [
            [ 'San Francisco', 'CA', '94105' ],
            [ 'Oakland', 'CA', '94607' ],
            [ 'Berkeley', 'CA', '94704' ],
            [ 'San Jose', 'CA', '95113' ],
            [ 'Austin', 'TX', '78701' ],
            [ 'Denver', 'CO', '80202' ],
        ];
        $city = $cities[ $index % count($cities) ];

        $profile = [
            'first_name' => (string) $first_name,
            'last_name'  => (string) $last_name,
            'phone'      => sprintf('555-%04d', 100 + ( $index % 9000 )),
            'address_1'  => sprintf('%d %s', 100 + ( $index * 17 ) % 800, $streets[ $index % count($streets) ]),
            'city'       => $city[0],
            'state'      => $city[1],
            'zip'        => $city[2],
            'country'    => 'US',
        ];

        // Every third profile gets a second address line (apartment / suite).
        if (0 === $index % 3) {
            $profile['address_2'] = sprintf('Suite %d', 100 + ( $index % 40 ));
        }

        return $profile;
    }

    /**
     * Leftover `_sc_*` user-meta keys written onto seeded customers.
     *
     * @return array<int, string>
     */
    public static function leftover_user_meta_key_list()
    {
        return [
            '_sc_customer_id',
            '_sc_stripe_customer_id',
            '_sc_phone',
            '_sc_address',
            '_sc_address_1',
            '_sc_address_2',
            '_sc_city',
            '_sc_state',
            '_sc_zip',
            '_sc_country',
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
        $keys   = array_merge($keys, self::leftover_user_meta_key_list());

        return array_values(array_unique($keys));
    }
}
