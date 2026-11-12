<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Artisan extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'category_id',
        'profile_photo',
        'cover_photo',
        'user_id',
        'experience_in_year',
        'website',
        'last_education',
        'date_of_birth',
        'profession',
        'main_profession',
        'profession_type',
        'skills',
        'hourly_rate',
        'rate_type',
        'currency',
        'is_active',
        'language_proficiencies',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'biography',
        'video_cv',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'skills' => 'array',
            'language_proficiencies' => 'array',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function rateTypeOptions(): array
    {
        return [
            'Per hour' => 'Per hour',
            'Per day' => 'Per day',
            'Per visit' => 'Per visit',
            'Per session' => 'Per session',
            'Per service' => 'Per service',
            'Per item' => 'Per item',
            'Per trip' => 'Per trip',
            'Per project' => 'Per project',
            'Per kilometer' => 'Per kilometer',
            'Fixed price' => 'Fixed price',
            'Package' => 'Package',
            'Price on request' => 'Price on request',
            'Weekly' => 'Weekly',
            'Monthly' => 'Monthly',
            'Free' => 'Free',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function currencySymbols(): array
    {
        return [
            'AED' => 'د.إ',
            'AFN' => '؋',
            'ALL' => 'L',
            'AMD' => '֏',
            'ANG' => 'ƒ',
            'AOA' => 'Kz',
            'ARS' => '$',
            'AUD' => 'A$',
            'AWG' => 'ƒ',
            'AZN' => '₼',
            'BAM' => 'KM',
            'BBD' => '$',
            'BDT' => '৳',
            'BGN' => 'лв',
            'BHD' => 'د.ب',
            'BIF' => 'FBu',
            'BMD' => '$',
            'BND' => '$',
            'BOB' => 'Bs.',
            'BRL' => 'R$',
            'BSD' => '$',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYN' => 'Br',
            'BZD' => 'BZ$',
            'CAD' => 'C$',
            'CDF' => 'FC',
            'CHF' => 'CHF',
            'CLP' => '$',
            'CNY' => '¥',
            'COP' => '$',
            'CRC' => '₡',
            'CUP' => '$',
            'CVE' => 'Esc',
            'CZK' => 'Kč',
            'DJF' => 'Fdj',
            'DKK' => 'kr',
            'DOP' => 'RD$',
            'DZD' => 'د.ج',
            'EGP' => 'ج.م',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '€',
            'FJD' => 'FJ$',
            'FKP' => '£',
            'GBP' => '£',
            'GEL' => '₾',
            'GHS' => '₵',
            'GIP' => '£',
            'GMD' => 'D',
            'GNF' => 'FG',
            'GTQ' => 'Q',
            'GYD' => 'GY$',
            'HKD' => 'HK$',
            'HNL' => 'L',
            'HTG' => 'G',
            'HUF' => 'Ft',
            'IDR' => 'Rp',
            'ILS' => '₪',
            'INR' => '₹',
            'IQD' => 'ع.د',
            'IRR' => '﷼',
            'ISK' => 'kr',
            'JMD' => 'J$',
            'JOD' => 'د.ا',
            'JPY' => '¥',
            'KES' => 'KSh',
            'KGS' => 'som',
            'KHR' => '៛',
            'KMF' => 'CF',
            'KPW' => '₩',
            'KRW' => '₩',
            'KWD' => 'د.ك',
            'KYD' => 'CI$',
            'KZT' => '₸',
            'LAK' => '₭',
            'LBP' => 'ل.ل',
            'LKR' => 'Rs',
            'LRD' => 'L$',
            'LSL' => 'L',
            'LYD' => 'ل.د',
            'MAD' => 'د.م.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => 'ден',
            'MMK' => 'K',
            'MNT' => '₮',
            'MOP' => 'MOP$',
            'MRU' => 'UM',
            'MUR' => '₨',
            'MVR' => 'Rf',
            'MWK' => 'MK',
            'MXN' => 'MX$',
            'MYR' => 'RM',
            'MZN' => 'MT',
            'NAD' => 'N$',
            'NGN' => '₦',
            'NIO' => 'C$',
            'NOK' => 'kr',
            'NPR' => '₨',
            'NZD' => 'NZ$',
            'OMR' => 'ر.ع.',
            'PAB' => 'B/.',
            'PEN' => 'S/',
            'PGK' => 'K',
            'PHP' => '₱',
            'PKR' => '₨',
            'PLN' => 'zł',
            'PYG' => '₲',
            'QAR' => 'ر.ق',
            'RON' => 'lei',
            'RSD' => 'дин.',
            'RUB' => '₽',
            'RWF' => 'FRw',
            'SAR' => 'ر.س',
            'SBD' => 'SI$',
            'SCR' => '₨',
            'SDG' => 'ج.س',
            'SEK' => 'kr',
            'SGD' => 'S$',
            'SHP' => '£',
            'SLE' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '$',
            'SSP' => 'SS£',
            'STN' => 'Db',
            'SVC' => '$',
            'SYP' => '£',
            'SZL' => 'E',
            'THB' => '฿',
            'TJS' => 'SM',
            'TMT' => 'm',
            'TND' => 'د.ت',
            'TOP' => 'T$',
            'TRY' => '₺',
            'TTD' => 'TT$',
            'TWD' => 'NT$',
            'TZS' => 'TSh',
            'UAH' => '₴',
            'UGX' => 'USh',
            'USD' => '$',
            'UYU' => '$U',
            'UZS' => "so'm",
            'VES' => 'Bs.',
            'VND' => '₫',
            'VUV' => 'VT',
            'WST' => 'WS$',
            'XAF' => 'FCFA',
            'XCD' => 'EC$',
            'XOF' => 'CFA',
            'XPF' => '₣',
            'YER' => '﷼',
            'ZAR' => 'R',
            'ZMW' => 'ZK',
            'ZWL' => 'Z$',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function currencyOptions(): array
    {
        $options = [];

        foreach (static::currencySymbols() as $code => $symbol) {
            $options[$code] = "{$code} ({$symbol})";
        }

        ksort($options);

        return $options;
    }

    public static function currencySymbol(?string $code): string
    {
        $code = strtoupper((string) $code);

        if ($code === '') {
            return '€';
        }

        return static::currencySymbols()[$code] ?? $code;
    }

    public static function currencyPrefix(?string $code): string
    {
        $symbol = static::currencySymbol($code);

        return ctype_alpha($symbol) ? $symbol.' ' : $symbol;
    }

    public static function rateSuffix(?string $rateType): string
    {
        return match ($rateType) {
            'Per hour' => '/hour',
            'Per day' => '/day',
            'Per visit' => '/visit',
            'Per session' => '/session',
            'Per service' => '/service',
            'Per item' => '/item',
            'Per trip' => '/trip',
            'Per project' => '/project',
            'Per kilometer' => '/km',
            'Weekly' => '/week',
            'Monthly' => '/month',
            default => '',
        };
    }

    public static function rateTypeUsesAmount(?string $rateType): bool
    {
        return ! in_array($rateType, ['Free', 'Price on request'], true);
    }

    public function priceLabel(): ?string
    {
        $rateType = $this->rate_type ?? 'Per hour';

        if ($rateType === 'Free') {
            return 'Free';
        }

        if ($rateType === 'Price on request') {
            return 'Price on request';
        }

        if ($this->hourly_rate === null) {
            return null;
        }

        $amount = rtrim(rtrim(number_format((float) $this->hourly_rate, 2, '.', ''), '0'), '.');

        return trim("{$this->currency} {$amount}".static::rateSuffix($rateType));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function contactDetail(): HasOne
    {
        return $this->hasOne(ContactDetail::class, 'user_id', 'user_id');
    }

    public function queries(): HasMany
    {
        return $this->hasMany(ArtisanQuery::class);
    }

    public function unreadQueries(): HasMany
    {
        return $this->queries()->unread();
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'artisan_user')->wherePivot('reaction', '=', 'like');
    }

    public function dislikers()
    {
        return $this->belongsToMany(User::class, 'artisan_user')->wherePivot('reaction', '=', 'dislike');
    }

    public function likeCount(): int
    {
        return (int) ($this->getAttribute('likers_count') ?? $this->likers()->count());
    }

    public function dislikeCount(): int
    {
        return (int) ($this->getAttribute('dislikers_count') ?? $this->dislikers()->count());
    }

    public function satisfactionPercentage(): int
    {
        $total = $this->likeCount() + $this->dislikeCount();

        return $total === 0 ? 0 : (int) round($this->likeCount() / $total * 100);
    }

    public function experienceYears(): int
    {
        return (int) $this->experience_in_year;
    }

    public function locationLabel(): string
    {
        return collect([$this->country?->name, $this->state?->name, $this->city?->name])
            ->filter()
            ->implode(', ');
    }
}
