<?php


namespace Bytes\Common\Faker\Providers;


use Bytes\DiscordResponseBundle\Enums\ApplicationCommandOptionType;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandPermissionType;
use Bytes\DiscordResponseBundle\Enums\ButtonStyle;
use Bytes\DiscordResponseBundle\Enums\ChannelTypes;
use Bytes\DiscordResponseBundle\Enums\Emojis;
use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\ChannelMention;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Embed\Field;
use Bytes\DiscordResponseBundle\Objects\Emoji;
use Bytes\DiscordResponseBundle\Objects\Message\Component;
use Bytes\DiscordResponseBundle\Objects\Message\SelectOption;
use Bytes\DiscordResponseBundle\Objects\MessageReference;
use Bytes\DiscordResponseBundle\Objects\PartialEmoji;
use Bytes\DiscordResponseBundle\Objects\Reaction;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOptionChoice;
use Bytes\DiscordResponseBundle\Objects\User;
use Exception;
use Faker\Generator;
use Faker\Provider\Address;
use Faker\Provider\Barcode;
use Faker\Provider\Base;
use Faker\Provider\Biased;
use Faker\Provider\Color;
use Faker\Provider\Company;
use Faker\Provider\DateTime;
use Faker\Provider\File;
use Faker\Provider\HtmlLorem;
use Faker\Provider\Image;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Faker\Provider\Medical;
use Faker\Provider\Miscellaneous;
use Faker\Provider\Payment;
use Faker\Provider\Person;
use Faker\Provider\PhoneNumber;
use Faker\Provider\Text;
use Faker\Provider\UserAgent;
use Faker\Provider\Uuid;
use Spatie\Enum\Faker\FakerEnumProvider;

/**
 * Class Discord
 * @package Bytes\Common\Faker\Providers
 *
 * @property Generator|MiscProvider|Address|Barcode|Biased|Color|Company|DateTime|File|HtmlLorem|Image|Internet|Lorem|Medical|Miscellaneous|Payment|Person|PhoneNumber|Text|UserAgent|Uuid|FakerEnumProvider $generator
 */
class Discord extends Base
{
    use SetupDependenciesTrait;

    /**
     *
     */
    const REGIONS = [
        'brazil',
        'europe',
        'hong-kong',
        'india',
        'japan',
        'russia',
        'singapore',
        'south-africa',
        'sydney',
        'us-central',
        'us-east',
        'us-south',
        'us-west',
    ];

    /**
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        self::addProviderIfNeeded(MiscProvider::class, $generator);
        self::addProviderIfNeeded(FakerEnumProvider::class, $generator);
        parent::__construct($generator);
    }

    /**
     * @param bool $isGif
     * @return string
     */
    public function iconHash(bool $isGif = false)
    {
        $output = '';
        foreach (range(1, 6) as $index) {
            $output .= str_pad(dechex(self::numberBetween(1, 16777215)), 6, '0', STR_PAD_LEFT);
        }
        return ($isGif ? 'a_' : '') . substr($output, 0, 32);
    }

    //region Object Ids

    /**
     * @return string
     */
    public function guildId()
    {
        return self::snowflake(7);
    }

    /**
     * @param int|null $prepend
     * @return string
     */
    public function snowflake(?int $prepend = null)
    {
        $output = '';
        if (!is_null($prepend)) {
            $output = (string)$prepend;
        }
        foreach (range(1, 3) as $index) {
            $output .= (string)$this->generator->numberBetween(100000, 999999);
        }
        return substr($output, 0, 18);
    }

    /**
     * @return string
     */
    public function roleId()
    {
        return self::snowflake(8);
    }

    /**
     * @return string
     */
    public function userId()
    {
        return self::snowflake(2);
    }

    /**
     * @return string
     */
    public function channelId()
    {
        return self::snowflake(2);
    }

    /**
     * @return string
     */
    public function messageId()
    {
        return self::snowflake(8);
    }
    //endregion

    /**
     * @return string
     */
    public function guildName()
    {
        return $this->generator->text(100);
    }

    /**
     * @return bool
     */
    public function owner()
    {
        return $this->generator->boolean();
    }

    /**
     * @param int $maxFeatures
     * @return string[]
     */
    public function features(int $maxFeatures = 3)
    {
        return $this->generator->words($maxFeatures);
    }

    /**
     * [username]#[discriminator]
     * @return string
     */
    public function userNameDiscriminator()
    {
        return $this->generator->userName() . '#' . self::discriminator();
    }

    /**
     * Zero-padded four digit number
     * @return string
     */
    public function discriminator()
    {
        return str_pad($this->generator->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    //region Application Commands

    /**
     * @return ApplicationCommand
     */
    public function applicationCommand()
    {
        $options = [];
        if($this->generator->boolean())
        {
            $options[] = self::applicationCommandOption(true);
        }
        foreach($this->generator->rangeBetween() as $index) {
            $options[] = self::applicationCommandOption(false);
        }
        return ApplicationCommand::create($this->generator->word(), $this->generator->words(3, true), $options);
    }

    /**
     * @param bool|null $required
     * @return ApplicationCommandOption
     */
    public function applicationCommandOption(?bool $required = null)
    {
        $type = ApplicationCommandOptionType::make($this->generator->valid(function ($value) {
            return $value > 2;
        })->randomElement(ApplicationCommandOptionType::toValues()));
        $choices = [];
        if ($this->generator->boolean(75) && ($type == ApplicationCommandOptionType::string() || $type == ApplicationCommandOptionType::integer())) {
            foreach ($this->generator->rangeBetween(4, 1, 2) as $index) {
                $choice = self::applicationCommandOptionChoice($type);
                if (!is_null($choice)) {
                    $choices[] = $choice;
                }
            }
        }
        return ApplicationCommandOption::create($type, $this->generator->word(), $this->generator->words(3, true), is_null($required) ? $this->generator->boolean() : $required, $choices);
    }

    /**
     * @param ApplicationCommandOptionType $type
     * @return ApplicationCommandOptionChoice
     */
    public function applicationCommandOptionChoice(ApplicationCommandOptionType $type) {
        switch ($type) {
            case ApplicationCommandOptionType::boolean():
                $value = $this->generator->boolean();
                break;
            case ApplicationCommandOptionType::integer():
            case ApplicationCommandOptionType::number():
                $value = $this->generator->randomDigitNotNull();
                break;
            case ApplicationCommandOptionType::string():
                $value = $this->generator->word();
                break;
            default:
                return null;
                break;
        }
        return ApplicationCommandOptionChoice::create($this->generator->word(), $value);
    }

    /**
     * Returns a ApplicationCommandPermissionType value
     * @return ApplicationCommandPermissionType
     */
    public function applicationCommandPermissionType(): ApplicationCommandPermissionType
    {
        return $this->generator->randomEnum(ApplicationCommandPermissionType::class);
    }
    //endregion

    //region Token

    /**
     * @return string
     */
    public function refreshToken()
    {
        return self::accessToken();
    }

    /**
     * @return string
     */
    public function accessToken()
    {
        return $this->generator->randomAlphanumericString(30);
    }

    /**
     * @return string
     */
    public function tokenType()
    {
        return $this->generator->randomElement(['Bot', 'Bearer']);
    }

    /**
     * @return string
     */
    public function scope()
    {
        $temp = self::scopes(1);
        return array_shift($temp);
    }

    /**
     * @param int $max
     * @return string[]
     */
    public function scopes(int $max = 0)
    {
        $permissions = [
            'bot',
            'connections',
            'email',
            'identify',
            'guilds',
            'guilds.join',
            'gdm.join',
            'messages.read',
            'rpc',
            'rpc.api',
            'rpc.notifications.read',
            'webhook.incoming',
            'applications.builds.upload',
            'applications.builds.read',
            'applications.store.update',
            'applications.entitlements',
            'relationships.read',
            'activities.read',
            'activities.write',
            'applications.commands',
            'applications.commands.update',
        ];
        if ($max < 1) {
            $max = $this->generator->numberBetween(1, count($permissions));
        }
        return $this->generator->randomElements($permissions, $max);
    }

    /**
     * @return int
     */
    public function permissionInteger()
    {
        $permissions = [
            0x00000001,
            0x00000002,
            0x00000004,
            0x00000008,
            0x00000010,
            0x00000020,
            0x00000040,
            0x00000080,
            0x00000100,
            0x00000200,
            0x00000400,
            0x00000800,
            0x00001000,
            0x00002000,
            0x00004000,
            0x00008000,
            0x00010000,
            0x00020000,
            0x00040000,
            0x00080000,
            0x00100000,
            0x00200000,
            0x00400000,
            0x00800000,
            0x01000000,
            0x02000000,
            0x04000000,
            0x08000000,
            0x10000000,
            0x20000000,
            0x40000000,
        ];

        $return = 0;
        foreach ($this->generator->randomElements($permissions, $this->generator->numberBetween(0, count($permissions))) as $i) {
            $v = $i;
            $return |= $v;
        }
        return $return;
    }
    //endregion

    /**
     * @return int
     */
    public function channelType()
    {
        return $this->generator->numberBetween(0, 6);
    }

    /**
     * @return string
     */
    public function region()
    {
        return $this->generator->randomElement(self::REGIONS);
    }

    /**
     * @return string|null
     */
    public function rtcRegion()
    {
        return $this->generator->optional(self::rtcRegionNullProbability())->region();
    }

    /**
     * @return float
     */
    public static function rtcRegionNullProbability()
    {
        return 1 - (1 / (count(self::REGIONS) + 1));
    }

    /**
     * @param bool $excludeV6
     */
    public function messageType(bool $excludeV8 = false)
    {
        $types = range(0, 12);
        $types[] = 14;
        $types[] = 15;
        if (!$excludeV8) {
            $types[] = 19;
            $types[] = 20;
        }
        return $this->generator->randomElement($types);
    }

    /**
     * @param int|float|null $weight Optional weight for optional method. If null, optional is omitted
     * @return string|null
     */
    public function timestamp($weight = null)
    {
        if (is_null($weight)) {
            return $this->generator->dateTimeThisMonth()->format(DATE_ATOM);
        }
        $date = $this->generator->optional($weight)->dateTimeThisMonth();

        if (is_null($date)) {
            return $date;
        }
        return $date->format(DATE_ATOM);
    }

    /**
     * @return array
     */
    public function filter()
    {
        $options = array_merge(['around', 'before', 'after'], $this->generator->unique()->words(5));
        $this->generator->unique(true);
        return $this->generator->randomElements($options, count($options), false);
    }

    //region Embeds

    /**
     * @return Embed[]|null
     */
    public function embeds(int $max = 3)
    {
        foreach ($this->generator->rangeBetween($max) as $item) {
            $embeds[] = self::embed();
        }
        return $embeds;
    }

    /**
     * @return Embed|null
     */
    public function embed()
    {
        $embed = new Embed();

        try {
            $embed->setUrl($this->generator->optional()->url());
            $embed->setFooter($this->generator->sentence(), $this->generator->url());
            $embed->setAuthor($this->generator->sentence(), $this->generator->url(), $this->generator->imageUrl());
            $embed->setTitle($this->generator->sentence());
            $embed->setColor(self::embedColor());
            $embed->setThumbnail($this->generator->imageUrl());
            foreach ($this->generator->oneOrMoreOf([1, 2]) as $index) {
                $embed->addField($this->generator->sentence(3), $this->generator->sentence(3), $this->generator->boolean());
            }
            foreach ($this->generator->oneOrMoreOf([1, 2]) as $index) {
                $field = new Field();
                $field->setName($this->generator->sentence(3));
                $field->setValue($this->generator->sentence(3));
                $field->setInline($this->generator->boolean());
                $embed->addField($field);
            }
        } catch (Exception $x) {
            return null;
        }

        return $embed;
    }

    /**
     * @return float|int
     */
    public static function embedColor()
    {
        return hexdec('0x' . str_pad(dechex(self::numberBetween(1, 16777215)), 6, '0', STR_PAD_LEFT));
    }
    //endregion

    //region Mock Rate Limits

    /**
     * @return array
     */
    public function rateLimitArray(bool $noRemaining = false)
    {
        $reset = $this->generator->dateTimeInInterval('now', '+3 seconds')->getTimestamp();
        $now = new \DateTime();

        $info = [
            'X-RateLimit-Bucket' => self::rateLimitBucket(),
            'X-RateLimit-Limit' => self::rateLimit(),
            'X-RateLimit-Remaining' => 4,
            'X-RateLimit-Reset' => $reset,
            'X-RateLimit-Reset-After' => $reset - ($now->getTimestamp()) + $this->generator->randomFloat(3, 0, 0.9),
        ];
        if ($noRemaining) {
            $info['X-RateLimit-Remaining'] = 0;
        } else {
            $info['X-RateLimit-Remaining'] = self::rateLimit($info['X-RateLimit-Limit']);
        }

        return $info;
    }

    /**
     * @return string
     */
    public function rateLimitBucket()
    {
        return $this->generator->randomAlphanumericString(8);
    }

    /**
     * @return int
     */
    public function rateLimit(int $max = 60)
    {
        return $this->generator->numberBetween(5, $max);
    }

    /**
     * @return float
     */
    public function rateLimitResetAfter()
    {
        return $this->generator->randomFloat(3, 1, 60);
    }
    //endregion

    //region Emoji

    /**
     * @return string
     */
    public function emoji()
    {
        return $this->generator->randomElement([
            self::globalEmoji(),
            self::customEmoji(),
        ]);
    }

    /**
     * @return string
     */
    public function globalEmoji()
    {
        return $this->generator->randomEnumValue(Emojis::class);
    }

    /**
     * @return string
     */
    public function customEmoji()
    {
        return sprintf(':%s:%s', $this->generator->word(), self::snowflake());
    }

    /**
     * @return Emoji
     */
    public function emojiObject()
    {
        $emoji = new Emoji();
        $emoji->setId(self::snowflake())
            ->setName($this->generator->word());

        return $emoji;
    }

    /**
     * @return PartialEmoji
     */
    public function partialEmoji()
    {
        return PartialEmoji::create(self::snowflake(), $this->generator->word());
    }
    //endregion

    //region Objects
    /**
     * @return User
     * @throws Exception
     */
    public function user()
    {
        $user = new User();
        $user->setId(self::userId())
            ->setUsername($this->generator->userName())
            ->setAvatar(self::iconHash())
            ->setDiscriminator(self::discriminator())
            ->setPublicFlags($this->generator->randomDigit());

        return $user;
    }

    /**
     * @return ChannelMention
     */
    public function channelMention()
    {
        $channel = new ChannelMention();
        $channel->setId(self::snowflake())
            ->setGuildId(self::guildId())
            ->setType($this->generator->randomEnumValue(ChannelTypes::class))
            ->setName($this->generator->sentence());

        return $channel;
    }

    /**
     * @return Reaction
     */
    public function reaction()
    {
        $reaction = new Reaction();
        $reaction->setEmoji(self::emojiObject())
            ->setCount($this->generator->randomDigitNotNull())
            ->setMe($this->generator->boolean());

        return $reaction;
    }

    /**
     * @param int $max
     * @return Reaction[]|null
     */
    public function reactions(int $max = 3)
    {
        foreach ($this->generator->rangeBetween($max) as $item) {
            $reactions[] = self::reaction();
        }
        return $reactions;
    }

    /**
     * @return MessageReference
     */
    public function messageReference()
    {
        $ref = new MessageReference();
        $ref->setMessageId(self::messageId())
            ->setChannelID(self::channelId())
            ->setGuildId(self::guildId())
            ->setFailIfNotExists($this->generator->boolean());

        return $ref;
    }

    /**
     * @return Channel
     */
    public function channel()
    {
        $channel = new Channel();
        $channel->setId(self::snowflake())
            ->setGuildId(self::guildId())
            ->setType($this->generator->randomEnumValue(ChannelTypes::class))
            ->setName($this->generator->sentence());

        return $channel;
    }

    /**
     * @return Component
     */
    public function componentSelectMenu()
    {
        return Component::createSelectMenu($this->generator->word(), options: self::componentSelectOptions());
    }

    /**
     * @return SelectOption
     */
    public function componentSelectOption()
    {
        return SelectOption::create($this->generator->sentence(), $this->generator->word(), $this->generator->sentence(), self::partialEmoji(), $this->generator->boolean());
    }

    /**
     * @param int $max
     * @return SelectOption[]|null
     */
    public function componentSelectOptions(int $max = 3)
    {
        $options = [];
        foreach ($this->generator->rangeBetween($max) as $item) {
            $options[] = self::componentSelectOption();
        }
        return $options;
    }

    /**
     * @return Component
     */
    public function componentInteractiveButton()
    {
        return Component::createInteractiveButton(style: $this->generator->randomElement([
            ButtonStyle::primary(), ButtonStyle::secondary(), ButtonStyle::danger(), ButtonStyle::success()
        ]), customId: $this->generator->word(), label: $this->generator->sentence(), emoji: self::partialEmoji(),
            disabled: $this->generator->boolean());
    }

    /**
     * @return Component
     */
    public function componentLinkButton()
    {
        return Component::createLinkButton($this->generator->url(), label: $this->generator->sentence(), emoji: self::partialEmoji(), disabled: $this->generator->boolean());
    }

    /**
     * @param int $max
     * @return Component[]|null
     */
    public function componentButtons(int $max = 3)
    {
        $options = [];
        foreach ($this->generator->rangeBetween($max) as $item) {
            $options[] = $this->generator->randomElement([
                self::componentLinkButton(),
                self::componentInteractiveButton(),
                ]);
        }
        return $options;
    }

    /**
     * @return Component
     */
    public function componentActionRow()
    {
        return Component::createActionRow([
            $options[] = $this->generator->randomElement([
                self::componentSelectMenu(),
                self::componentButtons(),
            ])
        ]);
    }

    /**
     * @param int $max
     * @return Component[]|null
     */
    public function componentActionRows(int $max = 3)
    {
        $options = [];
        foreach ($this->generator->rangeBetween($max) as $item) {
            $options[] = self::componentActionRow();
        }
        return $options;
    }
    //endregion
}