<?php

namespace Bytes\Common\Faker\Tests\Providers;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Objects\Message\SelectOption;
use Bytes\DiscordResponseBundle\Objects\Reaction;
use PHPUnit\Framework\TestCase;

class DiscordTest extends TestCase
{
    use TestDiscordFakerTrait;

    /**
     *
     */
    public function testSnowflake()
    {
        $snowflake = $this->faker->snowflake();

        $this->assertMatchesRegularExpression('/^[0-9]{18,18}$/', $snowflake);

        $length = strlen($snowflake);
        $this->assertEquals(18, $length);
    }

    /**
     *
     */
    public function testSnowflakeWithPrepend()
    {
        $snowflake = $this->faker->snowflake(3);

        $this->validateSnowflakeWithPrepend($snowflake, 3);
    }

    /**
     * @param string $snowflake
     * @param string|int $prepend
     */
    private function validateSnowflakeWithPrepend($snowflake, $prepend)
    {
        $this->assertStringStartsWith($prepend, $snowflake);
        $this->assertMatchesRegularExpression('/^[0-9]{18,18}$/', $snowflake);

        $length = strlen($snowflake);
        $this->assertEquals(18, $length);
    }

    /**
     *
     */
    public function testGuildId()
    {
        $this->validateSnowflakeWithPrepend($this->faker->guildId(), 7);
    }

    /**
     *
     */
    public function testChannelId()
    {
        $this->validateSnowflakeWithPrepend($this->faker->channelId(), 2);
    }

    /**
     *
     */
    public function testMessageId()
    {
        $this->validateSnowflakeWithPrepend($this->faker->messageId(), 8);
    }

    /**
     *
     */
    public function testRoleId()
    {
        $this->validateSnowflakeWithPrepend($this->faker->roleId(), 8);
    }

    /**
     *
     */
    public function testUserId()
    {
        $this->validateSnowflakeWithPrepend($this->faker->userId(), 2);
    }

    /**
     *
     */
    public function testComponentSelectOptions()
    {
        $v = $this->faker->componentSelectOptions();
        $this->validateArray($v);
    }

    /**
     * @param array|null $v
     * @param int $max
     */
    private function validateArray(?array $v, int $max = 3): void
    {
        $count = $this->count($v);

        $this->assertGreaterThan(0, $count);
        $this->assertLessThanOrEqual($max, $count);
    }

    /**
     *
     */
    public function testReactions()
    {
        $v = $this->faker->reactions();
        $this->validateArray($v);
    }

    /**
     *
     */
    public function testScopes()
    {
        $v = $this->faker->scopes(3);
        $this->validateArray($v);
    }

    /**
     *
     */
    public function testReaction()
    {
        $reaction = $this->faker->reaction();

        $this->assertInstanceOf(Reaction::class, $reaction);
    }

    /**
     *
     */
    public function testUserNameDiscriminator()
    {
        $discriminator = $this->faker->userNameDiscriminator();

        $this->assertStringMatchesFormat('%s#%c%c%c%c', $discriminator);
    }

    /**
     *
     */
    public function testScope()
    {
        $component = $this->faker->scope();

        $this->assertIsString($component);
    }

    /**
     *
     */
    public function testComponentSelectOption()
    {
        $component = $this->faker->componentSelectOption();

        $this->assertInstanceOf(SelectOption::class, $component);
    }

    /**
     *
     */
    public function testDiscriminator()
    {
        $discriminator = $this->faker->discriminator();

        $this->assertMatchesRegularExpression('/[0-9][0-9][0-9][0-9]/', $discriminator);
        $this->assertStringMatchesFormat('%c%c%c%c', $discriminator);
    }
}