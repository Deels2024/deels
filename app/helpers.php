<?php declare(strict_types=1);

/**
 * @return mixed
 *               Custom functions made by themeqx
 */

use App\Ad;
use App\Language;
use App\Models\Country;
use App\Models\Media;
use App\Models\Option;
use App\Models\Post;
use App\Models\WithdrawalPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/laravel_helpers.php';

function deels_normalize_phone(?string $phone): ?string
{
    if (!$phone) {
        return null;
    }

    $digits = preg_replace('/\D+/', '', $phone);
    if (!$digits) {
        return null;
    }

    if (strlen($digits) === 10 && $digits[0] === '9') {
        $digits = '7' . $digits;
    }

    if (strlen($digits) === 11 && $digits[0] === '8') {
        $digits = '7' . substr($digits, 1);
    }

    if (strlen($digits) < 11 || strlen($digits) > 15 || $digits[0] === '0') {
        return null;
    }

    return '+' . $digits;
}

function deels_phone_hash(?string $phone): ?string
{
    $normalizedPhone = deels_normalize_phone($phone);

    return $normalizedPhone ? hash('sha256', $normalizedPhone) : null;
}

function slider_url($img = '')
{
    $url_path = '';
    if ($img) {
        if ('public' == $img->storage) {
            $url_path = asset('uploads/sliders/' . $img->media_name);
        } elseif ('s3' == $img->storage) {
            $url_path = Storage::disk('s3')->url('uploads/sliders/' . $img->media_name);
        }
    }

    return $url_path;
}

function avatar_img_url($img, $source)
{
    $url_path = '';
    if ($img) {
        if ('public' == $source) {
            $url_path = asset('uploads/avatar/' . $img);
        } elseif ('s3' == $source) {
            $url_path = Storage::disk('s3')->url('uploads/avatar/' . $img);
        }
    }

    return $url_path;
}

/**
 * @return string
 * @return logo   url
 */
function logo_url()
{
    return media_image_uri(get_option('logo'))->original;
}

function get_option(string $optionKey = '', bool $no_cache = false)
{
    try {
        if($no_cache) {
            $option =  DB::table('options')
                ->get()
                ->pluck('option_value', 'option_key')->toArray() ?? null;
            return (empty($option) ? config($optionKey) : $option[$optionKey] ?? null) ?? env($optionKey);
        }
        $option = Cache::remember(
            'context_options',
            240,
            fn() => DB::table('options')
                      ->get()
                      ->pluck('option_value', 'option_key')->toArray()
        );
    } catch (Exception) {
    }

    return (empty($option) ? config($optionKey) : $option[$optionKey] ?? null) ?? env($optionKey);
}

/**
 * @param  string  $text
 *
 * @return mixed
 */
function get_text_tpl($text = '')
{
    $tpl      = ['[year]', '[copyright_sign]', '[site_name]'];
    $variable = [date('Y'), '&copy;', get_option('site_name')];

    $tpl_option = str_replace($tpl, $variable, $text);

    return $tpl_option;
}

/**
 * @param  string  $title
 * @param          $model
 *
 * @return string
 */
function unique_slug($title = '', $model = 'Campaign')
{
    $slug = str_slug($title);

    if (empty($slug)) {
        $string = mb_strtolower($title, 'UTF-8');
        $string = preg_replace("/[\/\.]/", ' ', $string);
        $string = preg_replace("/[\s-]+/", ' ', $string);
        $slug   = preg_replace("/[\s_]/", '-', $string);
    }

    // get unique slug...
    $nSlug = $slug;
    $i     = 0;

    $model = str_replace(' ', '', "App\Models\ " . $model);
    while ($model::whereSlug($nSlug)->count() > 0) {
        ++$i;
        $nSlug = $slug . '-' . $i;
    }
    if ($i > 0) {
        $newSlug = substr($nSlug, 0, strlen($slug)) . '-' . $i;
    } else {
        $newSlug = $slug;
    }

    return $newSlug;
}

function get_languages()
{
    $languages = Language::all();

    return $languages;
}

/**
 * @param  string  $type
 *
 * @return string
 * @return stripe secret key or test key
 */
function get_stripe_key($type = 'publishable')
{
    $stripe_key = '';

    if ('publishable' == $type) {
        if (1 == get_option('stripe_test_mode')) {
            $stripe_key = get_option('stripe_test_publishable_key');
        } else {
            $stripe_key = get_option('stripe_live_publishable_key');
        }
    } elseif ('secret' == $type) {
        if (1 == get_option('stripe_test_mode')) {
            $stripe_key = get_option('stripe_test_secret_key');
        } else {
            $stripe_key = get_option('stripe_live_secret_key');
        }
    }

    return $stripe_key;
}

/**
 * @param  int     $ad_id
 * @param  string  $status
 */
function ad_status_change($ad_id = 0, $status = '1')
{
    if ($ad_id > 0) {
        $ad = Ad::find($ad_id);

        if ($ad) {
            $previous_status = $ad->status;
            // Publish ad
            $ad->status = $status;
            $ad->save();

            if (0 == $previous_status) {
                if (1 == $status) {
                    // Increase category product count by plus one
                    $category                = $ad->category;
                    $category->product_count = $category->product_count + 1;
                    $category->save();

                    // Increase Sub category product count by plus one
                    $sub_category                = $ad->sub_category;
                    $sub_category->product_count = $sub_category->product_count + 1;
                    $sub_category->save();

                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * @return bool
 *
 * return all fa icon list
 */
function fa_icons()
{
    $pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"\\\\(.+)";\s+}/';
    $subject = file_get_contents(public_path('assets/font-awesome-4.4.0/css/font-awesome.css'));
    preg_match_all($pattern, $subject, $matches, \PREG_SET_ORDER);

    foreach ($matches as $match) {
        $icons[$match[1]] = $match[2];
    }
    ksort($icons);

    return $icons;
}

function category_classes()
{
    $classes = [
        'green'  => 'green',
        'gold'   => 'gold',
        'purple' => 'purple',
        'orange' => 'orange',
        'brick'  => 'brick',
        'blue'   => 'blue',
        'honey'  => 'honey',
    ];

    return $classes;
}

/**
 * @param  int  $amount
 *
 * @return string
 */
function get_amount($amount = 0, $k = false, $coins = false)
{
    $show_price = '';

    $currency_position = get_option('currency_position');
    $currency_sign     = get_currency_symbol(get_option('currency_sign'));

    $get_price = get_amount_raw($amount);

    if ($k) {
        $get_price = round((float) str_replace(' ', '', $get_price) / 1000) . 'k';
    }

    if ('right' == $currency_position) {
        $show_price = $get_price . ' ' . $currency_sign;
    } else {
        if($coins) {
            $show_price = $get_price . ' <img src="/dist/img/deels_cur.svg" class="small_coin">';
        } else {
            $show_price = $get_price . ' <span class="ruble-sign">' . $currency_sign . '</span>';
        }
    }

    return $show_price;
}

function toMb(int $bytes): string
{
    return number_format($bytes / 1048576, 2) . ' MB';
}

function get_amount_raw($amount = 0)
{
    $get_price               = '0.00';
    $none_decimal_currencies = get_zero_decimal_currency();

    if (in_array(get_option('currency_sign'), $none_decimal_currencies)) {
        $get_price = (int) $amount;
    } elseif ($amount > 0) {
//        $get_price = number_format((float) $amount, 2, '.', ' ');
        $get_price = number_format((float) $amount, 0, '.', ' ');
    }

    return $get_price;
}

if (!function_exists('get_zero_decimal_currency')) {
    function get_zero_decimal_currency()
    {
        $zero_decimal_currency = [
            'BIF',
            'MGA',
            'CLP',
            'PYG',
            'DJF',
            'RWF',
            'GNF',
            'UGX',
            'JPY',
            'VND',
            'VUV',
            'KMF',
            'XAF',
            'KRW',
            'XOF',
            'XPF',
        ];

        return $zero_decimal_currency;
    }
}
if (!function_exists('get_stripe_amount')) {
    function get_stripe_amount($amount = 0, $type = 'to_cents')
    {
        if (!$amount) {
            return $amount;
        }

        $non_decimal_currency = get_zero_decimal_currency();

        if (in_array(get_option('currency_sign'), $non_decimal_currency)) {
            return $amount;
        }

        if ('to_cents' === $type) {
            return $amount * 100;
        }

        return $amount / 100;
    }
}

function update_option($key, $value)
{
    $option               = Option::firstOrCreate(['option_key' => $key]);
    $option->option_value = $value;

    return $option->save();
}

/**
 * @return null
 */
function withdrawal_preference($field = 'default_withdrawal_account')
{
    $user_id = Auth::user()->id;

    $withdrawal_preference = WithdrawalPreference::whereUserId($user_id)->first();

    if ('country_name' == $field) {
        $country = Country::find($withdrawal_preference->country_id);
        if ($country) {
            return $country->name;
        }
    }

    if ($withdrawal_preference) {
        return $withdrawal_preference->$field;
    }

    return null;
}

if (!function_exists('safe_output')) {
    function safe_output($text = null)
    {
        if ($text) {
            $text = strip_tags(
                $text,
                '<h1><h2><h3><h4><h5><h6><p><br><ul><li><hr><a><abbr><address><b><blockquote><center><cite><code><del><i><ins><strong><sub><sup><time><u><img><iframe><link><nav><ol><table><caption><th><tr><td><thead><tbody><tfoot><col><colgroup><div><span>'
            );

            $text = str_replace(['javascript:', 'http:'], ['', 'https:'], $text);
        }

        return $text;
    }
}

/**
 * @return array
 *
 * Get currencies
 */
function get_currencies()
{
    return [
        'AED' => 'United Arab Emirates dirham',
        'AFN' => 'Afghan afghani',
        'ALL' => 'Albanian lek',
        'AMD' => 'Armenian dram',
        'ANG' => 'Netherlands Antillean guilder',
        'AOA' => 'Angolan kwanza',
        'ARS' => 'Argentine peso',
        'AUD' => 'Australian dollar',
        'AWG' => 'Aruban florin',
        'AZN' => 'Azerbaijani manat',
        'BAM' => 'Bosnia and Herzegovina convertible mark',
        'BBD' => 'Barbadian dollar',
        'BDT' => 'Bangladeshi taka',
        'BGN' => 'Bulgarian lev',
        'BHD' => 'Bahraini dinar',
        'BIF' => 'Burundian franc',
        'BMD' => 'Bermudian dollar',
        'BND' => 'Brunei dollar',
        'BOB' => 'Bolivian boliviano',
        'BRL' => 'Brazilian real',
        'BSD' => 'Bahamian dollar',
        'BTC' => 'Bitcoin',
        'BTN' => 'Bhutanese ngultrum',
        'BWP' => 'Botswana pula',
        'BYR' => 'Belarusian ruble',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CDF' => 'Congolese franc',
        'CHF' => 'Swiss franc',
        'CLP' => 'Chilean peso',
        'CNY' => 'Chinese yuan',
        'COP' => 'Colombian peso',
        'CRC' => 'Costa Rican col&oacute;n',
        'CUC' => 'Cuban convertible peso',
        'CUP' => 'Cuban peso',
        'CVE' => 'Cape Verdean escudo',
        'CZK' => 'Czech koruna',
        'DJF' => 'Djiboutian franc',
        'DKK' => 'Danish krone',
        'DOP' => 'Dominican peso',
        'DZD' => 'Algerian dinar',
        'EGP' => 'Egyptian pound',
        'ERN' => 'Eritrean nakfa',
        'ETB' => 'Ethiopian birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian dollar',
        'FKP' => 'Falkland Islands pound',
        'GBP' => 'Pound sterling',
        'GEL' => 'Georgian lari',
        'GGP' => 'Guernsey pound',
        'GHS' => 'Ghana cedi',
        'GIP' => 'Gibraltar pound',
        'GMD' => 'Gambian dalasi',
        'GNF' => 'Guinean franc',
        'GTQ' => 'Guatemalan quetzal',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HNL' => 'Honduran lempira',
        'HRK' => 'Croatian kuna',
        'HTG' => 'Haitian gourde',
        'HUF' => 'Hungarian forint',
        'IDR' => 'Indonesian rupiah',
        'ILS' => 'Israeli new shekel',
        'IMP' => 'Manx pound',
        'INR' => 'Indian rupee',
        'IQD' => 'Iraqi dinar',
        'IRR' => 'Iranian rial',
        'ISK' => 'Icelandic kr&oacute;na',
        'JEP' => 'Jersey pound',
        'JMD' => 'Jamaican dollar',
        'JOD' => 'Jordanian dinar',
        'JPY' => 'Japanese yen',
        'KES' => 'Kenyan shilling',
        'KGS' => 'Kyrgyzstani som',
        'KHR' => 'Cambodian riel',
        'KMF' => 'Comorian franc',
        'KPW' => 'North Korean won',
        'KRW' => 'South Korean won',
        'KWD' => 'Kuwaiti dinar',
        'KYD' => 'Cayman Islands dollar',
        'KZT' => 'Kazakhstani tenge',
        'LAK' => 'Lao kip',
        'LBP' => 'Lebanese pound',
        'LKR' => 'Sri Lankan rupee',
        'LRD' => 'Liberian dollar',
        'LSL' => 'Lesotho loti',
        'LYD' => 'Libyan dinar',
        'MAD' => 'Moroccan dirham',
        'MDL' => 'Moldovan leu',
        'MGA' => 'Malagasy ariary',
        'MKD' => 'Macedonian denar',
        'MMK' => 'Burmese kyat',
        'MNT' => 'Mongolian t&ouml;gr&ouml;g',
        'MOP' => 'Macanese pataca',
        'MRO' => 'Mauritanian ouguiya',
        'MUR' => 'Mauritian rupee',
        'MVR' => 'Maldivian rufiyaa',
        'MWK' => 'Malawian kwacha',
        'MXN' => 'Mexican peso',
        'MYR' => 'Malaysian ringgit',
        'MZN' => 'Mozambican metical',
        'NAD' => 'Namibian dollar',
        'NGN' => 'Nigerian naira',
        'NIO' => 'Nicaraguan c&oacute;rdoba',
        'NOK' => 'Norwegian krone',
        'NPR' => 'Nepalese rupee',
        'NZD' => 'New Zealand dollar',
        'OMR' => 'Omani rial',
        'PAB' => 'Panamanian balboa',
        'PEN' => 'Peruvian nuevo sol',
        'PGK' => 'Papua New Guinean kina',
        'PHP' => 'Philippine peso',
        'PKR' => 'Pakistani rupee',
        'PLN' => 'Polish z&#x142;oty',
        'PRB' => 'Transnistrian ruble',
        'PYG' => 'Paraguayan guaran&iacute;',
        'QAR' => 'Qatari riyal',
        'RON' => 'Romanian leu',
        'RSD' => 'Serbian dinar',
        'RUB' => 'Russian ruble',
        'RWF' => 'Rwandan franc',
        'SAR' => 'Saudi riyal',
        'SBD' => 'Solomon Islands dollar',
        'SCR' => 'Seychellois rupee',
        'SDG' => 'Sudanese pound',
        'SEK' => 'Swedish krona',
        'SGD' => 'Singapore dollar',
        'SHP' => 'Saint Helena pound',
        'SLL' => 'Sierra Leonean leone',
        'SOS' => 'Somali shilling',
        'SRD' => 'Surinamese dollar',
        'SSP' => 'South Sudanese pound',
        'STD' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
        'SYP' => 'Syrian pound',
        'SZL' => 'Swazi lilangeni',
        'THB' => 'Thai baht',
        'TJS' => 'Tajikistani somoni',
        'TMT' => 'Turkmenistan manat',
        'TND' => 'Tunisian dinar',
        'TOP' => 'Tongan pa&#x2bb;anga',
        'TRY' => 'Turkish lira',
        'TTD' => 'Trinidad and Tobago dollar',
        'TWD' => 'New Taiwan dollar',
        'TZS' => 'Tanzanian shilling',
        'UAH' => 'Ukrainian hryvnia',
        'UGX' => 'Ugandan shilling',
        'USD' => 'United States dollar',
        'UYU' => 'Uruguayan peso',
        'UZS' => 'Uzbekistani som',
        'VEF' => 'Venezuelan bol&iacute;var',
        'VND' => 'Vietnamese &#x111;&#x1ed3;ng',
        'VUV' => 'Vanuatu vatu',
        'WST' => 'Samoan t&#x101;l&#x101;',
        'XAF' => 'Central African CFA franc',
        'XCD' => 'East Caribbean dollar',
        'XOF' => 'West African CFA franc',
        'XPF' => 'CFP franc',
        'YER' => 'Yemeni rial',
        'ZAR' => 'South African rand',
        'ZMW' => 'Zambian kwacha',
    ];
}

/**
 * Get Currency symbol.
 *
 * @param  string  $currency  (default: '')
 *
 * @return string
 */
if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currency = '')
    {
        if (!$currency) {
            $currency = 'RUB';
        }

        $symbols = [
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&fnof;',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;. &#x645;.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '₽',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '&#36;',
            'XOF' => 'Fr',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        ];

        $currency_symbol = $symbols[$currency] ?? '';

        return $currency_symbol;
    }
}

if (!function_exists('paypal_ipn_verify')) {
    function paypal_ipn_verify()
    {
        $paypal_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        if (1 == get_option('enable_paypal_sandbox')) {
            $paypal_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        // STEP 1: read POST data
        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        $raw_post_data  = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost         = [];
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (2 == count($keyval)) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if (true == $get_magic_quotes_exists && 1 == get_magic_quotes_gpc()) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // STEP 2: POST IPN data back to PayPal to validate
        $ch = curl_init($paypal_action_url);
        curl_setopt($ch, \CURLOPT_HTTP_VERSION, \CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, \CURLOPT_POST, 1);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, \CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, \CURLOPT_HTTPHEADER, ['Connection: Close']);

        if (!($res = curl_exec($ch))) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // STEP 3: Inspect IPN validation result and act accordingly
        if (0 == strcmp($res, 'VERIFIED')) {
            return true;
        } else {
            if (0 == strcmp($res, 'INVALID')) {
                return false;
            }
        }
    }
}

if (!function_exists('current_disk')) {
    function current_disk()
    {
        $current_disk = Storage::disk(get_option('default_storage'));

        return $current_disk;
    }
}

/**
 * @param        $media_id
 * @param  bool  $echo
 *
 * @return string
 */
if (!function_exists('media_image_uri_attr')) {
    function media_image_uri_attr($media_id, $echo = false)
    {
        $get_all_size = media_image_uri($media_id);

        $attr = '';
        if (!empty($get_all_size)) {
            foreach ($get_all_size as $size => $uri) {
                $attr .= " data-image-size-{$size}={$uri} ";
            }
        }

        if ($echo) {
            echo $attr;
        } else {
            return $attr;
        }
    }
}

if (!function_exists('media_image_uri')) {
    function media_image_uri($media = 0, $slug = null)
    {
        $sizes             = config('media.size');
        $sizes['original'] = 'Original Image';

        foreach ($sizes as $img_size => $name) {
            $sizes[$img_size] = asset('uploads/placeholder-image.png');
        }

        if ($media) {
            if (!is_object($media)) {
                $media = Media::find($media);
            }

            if ($media) {
                // Getting resized images
                foreach ($sizes as $img_size => $name) {
                    if ('original' === $img_size) {
                        $thumb_size = '';
                    } else {
                        $thumb_size = $img_size . '/';
                    }

                    if (
                        $slug &&
                        $img_size === 'thumbnail' &&
                        file_exists(public_path("uploads/webp/thumbs/{$slug}.webp"))
                    ) {
                        $url_path = asset("uploads/webp/thumbs/{$slug}.webp");
                    } elseif (file_exists(public_path("uploads/storage_images/{$thumb_size}" . $media->slug_ext))) {
                        $url_path = asset("uploads/storage_images/{$thumb_size}" . $media->slug_ext);
                    } else {
                        $url_path = asset("uploads/images/{$thumb_size}" . $media->slug_ext);
                    }

                    if(!file_exists(public_path($url_path))) {
                        $thumb_size = '';
                        $url_path = asset("uploads/images/{$thumb_size}" . $media->slug_ext);
                    }

                    $sizes[$img_size] = $url_path;
                }
            }
        }
        //        dump($sizes, file_exists(str_replace(['https://kopiberi.ru', 'feature_image'], ['', 'thumbnail'], $sizes['feature_image'])));
        //        if (file_exists(str_replace(['https://kopiberi.ru', 'feature_image'], ['', 'thumbnail'], $sizes['feature_image']))) {
        //            $sizes['thumb'] = str_replace(['https://kopiberi.ru', 'feature_image'], ['', 'thumbnail'], $sizes['feature_image']);
        //        }

        return (object) $sizes;
    }
}

if (!function_exists('media_file_uri')) {
    function media_file_uri($media_id = 0)
    {
        $url_path = null;

        if ($media_id) {
            $media = Media::find($media_id);
            if ($media) {
                $source = get_option('default_storage');

                if ('public' == $source) {
                    $url_path = asset('uploads/' . $media->slug_ext);
                } elseif ('s3' == $source) {
                    $url_path = Storage::disk('s3')->url('uploads/' . $media->slug_ext);
                }
            }
        }

        return $url_path;
    }
}

if (!function_exists('image_upload_form')) {
    function image_upload_form($input_name = 'image_id', $current_image_id = '', $only_image = true): void
    {
        if (!$input_name) {
            $input_name = 'image_id';
        } ?>
        <div class="image-wrap">
            <a href="javascript:" data-toggle="filemanager">
                <?php
                $img_src    = '';
                $media_name = '';
                if ($current_image_id) {
                    if ($only_image) {
                        $img_src = media_image_uri($current_image_id)->thumbnail;
                    } else {
                        $media      = get_media($current_image_id);
                        $media_name = $media->name;
                        $img_src    = $media->media_icon_url();
                    }
                } else {
                    $img_src = asset('assets/images/upload-icon.png');
                } ?>
                <img src="<?php echo $img_src; ?>" alt="" class="img-fluid"/>

                <br/>


            </a>
            <input type="hidden" name="<?php echo $input_name; ?>" class="image-input"
                   value="<?php echo $current_image_id; ?>">

            <?php
            if ($only_image) {
                ?>
                <span class="text-muted" style="font-size: 0.8rem">
                    Preferred image size <br/>
                    w 1000px X h 700px
                </span>
                <?php
            } ?>

            <p class="upload_file_name"><?php echo $media_name; ?></p>

        </div>
        <?php
    }
}

/**
 * @param  int  $media_id
 *
 * @return bool
 *
 * Get media row object by ID
 */
if (!function_exists('get_media')) {
    function get_media($media_id = 0)
    {
        if ($media_id) {
            $media = Media::find($media_id);
            if ($media) {
                return $media;
            }
        }

        return false;
    }
}

if (!function_exists('get_post_url')) {
    function get_post_url($post_id = 0)
    {
        if (!$post_id) {
            return false;
        }
        $post = Post::find($post_id);
        if (!$post) {
            return false;
        }

        return route('single_page', $post->slug);
    }
}

if (!function_exists('avatar_by_email')) {
    function avatar_by_email($email = '', $s = 40, $d = 'mm', $r = 'g', $img = false, $atts = [])
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";

        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }

        return $url;
    }
}

if (!function_exists('ext_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool   $secure
     *
     * @return string
     */
    function ext_asset($path, $secure = null) {
        return app('url')->asset($path, $secure).asset_version($path);
    }
}

if (!function_exists('asset_version')) {
    /**
     * Return asset file version
     *
     * @param string $path File uri
     *
     * @return string version string(e.g.: ?v=12345678)
     */
    function asset_version($path) {
        $file_path = public_path().DIRECTORY_SEPARATOR.$path;

        return (file_exists($file_path) ? '?v='.filemtime($file_path) : '');
    }
}


if (!function_exists('get_image_mime_type')) {
    function get_image_mime_type(string $image_path):?string
    {
        $mimes  = [
            IMAGETYPE_GIF => "image/gif",
            IMAGETYPE_JPEG => "image/jpg",
            IMAGETYPE_PNG => "image/png",
            IMAGETYPE_SWF => "image/swf",
            IMAGETYPE_PSD => "image/psd",
            IMAGETYPE_BMP => "image/bmp",
            IMAGETYPE_TIFF_II => "image/tiff",
            IMAGETYPE_TIFF_MM => "image/tiff",
            IMAGETYPE_JPC => "image/jpc",
            IMAGETYPE_JP2 => "image/jp2",
            IMAGETYPE_JPX => "image/jpx",
            IMAGETYPE_JB2 => "image/jb2",
            IMAGETYPE_SWC => "image/swc",
            IMAGETYPE_IFF => "image/iff",
            IMAGETYPE_WBMP => "image/wbmp",
            IMAGETYPE_XBM => "image/xbm",
            IMAGETYPE_ICO => "image/ico"];

        try {
            if (($image_type = exif_imagetype($image_path))
                && (array_key_exists($image_type ,$mimes)))
            {
                return $mimes[$image_type];
            }
        } catch (Throwable $e) {
            if (($image_type = exif_imagetype(public_path($image_path)))
                && (array_key_exists($image_type ,$mimes)))
            {
                return $mimes[$image_type];
            }
        }

        return NULL;
    }
}

if (!function_exists('ru_plural_form')) {
    /**
     * Возвращает слово в корректной форме для числа:
     * [один, два-четыре, пять-и-больше]
     */
    function ru_plural_form(int $number, array $forms): string
    {
        $number = abs($number) % 100;
        $last = $number % 10;

        if ($number > 10 && $number < 20) {
            return $forms[2];
        }

        if ($last > 1 && $last < 5) {
            return $forms[1];
        }

        if ($last === 1) {
            return $forms[0];
        }

        return $forms[2];
    }
}

if (!function_exists('instagram_time_ago')) {
    /**
     * Формат "как в Instagram": n секунд, n минут, n часов, n дней, n недель, n месяцев, n лет.
     *
     * @param mixed $date
     */
    function instagram_time_ago($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $createdAt = $date instanceof \Carbon\CarbonInterface
                ? $date
                : \Carbon\Carbon::parse($date);
        } catch (Throwable $e) {
            return '';
        }

        $seconds = $createdAt->diffInSeconds(\Carbon\Carbon::now(), false);

        if ($seconds <= 0) {
            return '1 секунда';
        }

        if ($seconds < 60) {
            $value = $seconds;
            return $value . ' ' . ru_plural_form($value, ['секунда', 'секунды', 'секунд']);
        }

        if ($seconds < 3600) {
            $value = (int) floor($seconds / 60);
            return $value . ' ' . ru_plural_form($value, ['минута', 'минуты', 'минут']);
        }

        if ($seconds < 86400) {
            $value = (int) floor($seconds / 3600);
            return $value . ' ' . ru_plural_form($value, ['час', 'часа', 'часов']);
        }

        if ($seconds < 604800) {
            $value = (int) floor($seconds / 86400);
            return $value . ' ' . ru_plural_form($value, ['день', 'дня', 'дней']);
        }

        if ($seconds < 2592000) {
            $value = (int) floor($seconds / 604800);
            return $value . ' ' . ru_plural_form($value, ['неделя', 'недели', 'недель']);
        }

        if ($seconds < 31536000) {
            $value = (int) floor($seconds / 2592000);
            return $value . ' ' . ru_plural_form($value, ['месяц', 'месяца', 'месяцев']);
        }

        $value = (int) floor($seconds / 31536000);
        return $value . ' ' . ru_plural_form($value, ['год', 'года', 'лет']);
    }
}
