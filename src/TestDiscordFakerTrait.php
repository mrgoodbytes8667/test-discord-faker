<?php


namespace Bytes\Tests\Common\Faker;


use Bytes\Tests\Common\Faker\Providers\Discord;
use Bytes\Tests\Common\Faker\Providers\MiscProvider;
use Faker\Factory;
use Faker\Generator as FakerGenerator;
use Faker\Provider\Address;
use Faker\Provider\Barcode;
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

/**
 * Trait TestDiscordFakerTrait
 * @package Bytes\Tests\Common\Faker
 */
trait TestDiscordFakerTrait
{
    /**
     * @var Discord|FakerGenerator|MiscProvider|Address|Barcode|Biased|Color|Company|DateTime|File|HtmlLorem|Image|Internet|Lorem|Medical|Miscellaneous|Payment|Person|PhoneNumber|Text|UserAgent|Uuid
     */
    protected $faker;

    /**
     * @before
     */
    protected function setupFaker(): void
    {
        if (is_null($this->faker)) {
            /** @var FakerGenerator|Discord $faker */
            $faker = Factory::create();
            $faker->addProvider(new Discord($faker));
            $this->faker = $faker;
        }
    }

    /**
     * @after
     */
    protected function tearDownFaker(): void
    {
        $this->faker = null;
    }
}