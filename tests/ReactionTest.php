<?php

namespace Maize\Markable\Tests;

use Maize\Markable\Exceptions\InvalidMarkValueException;
use Maize\Markable\Models\Reaction;
use Maize\Markable\Tests\Enums\ReactionType;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\User;

class ReactionTest extends TestCase
{
    /** @test */
    public function cannot_add_an_invalid_reaction_value_null_fails()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->expectException(InvalidMarkValueException::class);
        Reaction::add($article, $user);
    }

    /** @test */
    public function cannot_add_an_invalid_reaction_value_fails()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->expectException(InvalidMarkValueException::class);
        Reaction::add($article, $user, 'not_valid_value');
    }

    /** @test */
    public function can_add_any_value_with_wildcard()
    {
        config()->set('markable.allowed_values.reaction', '*');

        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $user, 'random_value');
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'random_value',
        ]);
    }

    /** @test */
    public function can_add_a_reaction()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $users[0], 'reaction_1');
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[0]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_1',
        ]);

        Reaction::add($article, $users[0], 'reaction_2');
        $this->assertDatabaseCount($table, 2);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[0]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_2',
        ]);

        Reaction::add($article, $users[0], 'reaction_2');
        $this->assertDatabaseCount($table, 2);

        Reaction::add($article, $users[1], 'reaction_2');
        $this->assertDatabaseCount($table, 3);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[1]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_2',
        ]);
    }

    /** @test */
    public function can_remove_a_reaction()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $user, 'reaction_1');
        $this->assertDatabaseCount($table, 1);

        Reaction::remove($article, $user, 'not_valid_value');
        $this->assertDatabaseCount($table, 1);

        Reaction::remove($article, $user, 'reaction_1');
        $this->assertDatabaseCount($table, 0);
    }

    /** @test */
    public function can_count_reactions()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();
        $table = (new Reaction)->getTable();

        $this->assertEquals(0, Reaction::count($article, 'reaction_1'));

        Reaction::add($article, $users[0], 'reaction_1');
        $this->assertEquals(1, Reaction::count($article, 'reaction_1'));
        $this->assertEquals(0, Reaction::count($article, 'reaction_2'));

        Reaction::add($article, $users[0], 'reaction_1');
        Reaction::add($article, $users[1], 'reaction_1');
        $this->assertEquals(2, Reaction::count($article, 'reaction_1'));
        $this->assertEquals(0, Reaction::count($article, 'reaction_2'));
    }

    /** @test */
    public function can_check_if_user_has_reaction()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();
        $table = (new Reaction)->getTable();

        $this->assertFalse(Reaction::has($article, $users[0], 'reaction_1'));
        $this->assertFalse(Reaction::has($article, $users[1], 'reaction_1'));

        Reaction::add($article, $users[0], 'reaction_1');
        $this->assertTrue(Reaction::has($article, $users[0], 'reaction_1'));
        $this->assertFalse(Reaction::has($article, $users[1], 'reaction_1'));
    }

    /** @test */
    public function cannot_toggle_an_invalid_reaction_value_null_fails()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->expectException(InvalidMarkValueException::class);
        Reaction::toggle($article, $user);
    }

    /** @test */
    public function cannot_toggle_an_invalid_reaction_value_fails()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->expectException(InvalidMarkValueException::class);
        Reaction::toggle($article, $user, 'not_valid_value');
    }

    /** @test */
    public function can_add_a_reaction_with_backed_enum()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => ReactionType::Like->value,
        ]);
    }

    /** @test */
    public function can_has_a_reaction_with_backed_enum()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->assertFalse(Reaction::has($article, $user, ReactionType::Like));

        Reaction::add($article, $user, ReactionType::Like);
        $this->assertTrue(Reaction::has($article, $user, ReactionType::Like));
        $this->assertFalse(Reaction::has($article, $user, ReactionType::Love));
    }

    /** @test */
    public function can_remove_a_reaction_with_backed_enum()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 1);

        Reaction::remove($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 0);
    }

    /** @test */
    public function can_toggle_a_reaction_with_backed_enum()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::toggle($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 1);
        $this->assertTrue(Reaction::has($article, $user, ReactionType::Like));

        Reaction::toggle($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 0);
        $this->assertFalse(Reaction::has($article, $user, ReactionType::Like));
    }

    /** @test */
    public function can_count_reactions_with_backed_enum()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();

        $this->assertEquals(0, Reaction::count($article, ReactionType::Like));

        Reaction::add($article, $users[0], ReactionType::Like);
        Reaction::add($article, $users[1], ReactionType::Like);
        $this->assertEquals(2, Reaction::count($article, ReactionType::Like));
        $this->assertEquals(0, Reaction::count($article, ReactionType::Love));
    }

    /** @test */
    public function can_use_enum_class_as_allowed_values()
    {
        config()->set('markable.allowed_values.reaction', ReactionType::class);

        $article = Article::factory()->create();
        $user = User::factory()->create();
        $table = (new Reaction)->getTable();

        Reaction::add($article, $user, ReactionType::Like);
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, [
            'value' => ReactionType::Like->value,
        ]);
    }

    /** @test */
    public function cannot_add_invalid_value_when_allowed_values_is_enum_class()
    {
        config()->set('markable.allowed_values.reaction', ReactionType::class);

        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->expectException(InvalidMarkValueException::class);
        Reaction::add($article, $user, 'not_a_valid_enum_value');
    }

    /** @test */
    public function can_toggle_a_reaction()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();
        $table = (new Reaction)->getTable();

        Reaction::toggle($article, $users[0], 'reaction_1');
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[0]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_1',
        ]);

        Reaction::toggle($article, $users[0], 'reaction_2');
        $this->assertDatabaseCount($table, 2);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[0]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_2',
        ]);

        Reaction::toggle($article, $users[0], 'reaction_2');
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseMissing($table, [
            'user_id' => $users[0]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_2',
        ]);

        Reaction::toggle($article, $users[1], 'reaction_3');
        $this->assertDatabaseCount($table, 2);
        $this->assertDatabaseHas($table, [
            'user_id' => $users[1]->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => 'reaction_3',
        ]);
    }
}
