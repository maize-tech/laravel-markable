<?php

use Maize\Markable\Exceptions\InvalidMarkValueException;
use Maize\Markable\Models\Reaction;
use Maize\Markable\Tests\Enums\ReactionType;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\User;

it('cannot add an invalid reaction value null', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(fn () => Reaction::add($article, $user))->toThrow(InvalidMarkValueException::class);
});

it('cannot add an invalid reaction value', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(fn () => Reaction::add($article, $user, 'not_valid_value'))->toThrow(InvalidMarkValueException::class);
});

it('can add any value with wildcard', function () {
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
});

it('can add a reaction', function () {
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
});

it('can remove a reaction', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();
    $table = (new Reaction)->getTable();

    Reaction::add($article, $user, 'reaction_1');
    $this->assertDatabaseCount($table, 1);

    Reaction::remove($article, $user, 'not_valid_value');
    $this->assertDatabaseCount($table, 1);

    Reaction::remove($article, $user, 'reaction_1');
    $this->assertDatabaseCount($table, 0);
});

it('can count reactions', function () {
    $article = Article::factory()->create();
    $users = User::factory(2)->create();

    expect(Reaction::count($article, 'reaction_1'))->toEqual(0);

    Reaction::add($article, $users[0], 'reaction_1');
    expect(Reaction::count($article, 'reaction_1'))->toEqual(1);
    expect(Reaction::count($article, 'reaction_2'))->toEqual(0);

    Reaction::add($article, $users[0], 'reaction_1');
    Reaction::add($article, $users[1], 'reaction_1');
    expect(Reaction::count($article, 'reaction_1'))->toEqual(2);
    expect(Reaction::count($article, 'reaction_2'))->toEqual(0);
});

it('can check if user has reaction', function () {
    $article = Article::factory()->create();
    $users = User::factory(2)->create();

    expect(Reaction::has($article, $users[0], 'reaction_1'))->toBeFalse();
    expect(Reaction::has($article, $users[1], 'reaction_1'))->toBeFalse();

    Reaction::add($article, $users[0], 'reaction_1');
    expect(Reaction::has($article, $users[0], 'reaction_1'))->toBeTrue();
    expect(Reaction::has($article, $users[1], 'reaction_1'))->toBeFalse();
});

it('cannot toggle an invalid reaction value null', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(fn () => Reaction::toggle($article, $user))->toThrow(InvalidMarkValueException::class);
});

it('cannot toggle an invalid reaction value', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(fn () => Reaction::toggle($article, $user, 'not_valid_value'))->toThrow(InvalidMarkValueException::class);
});

it('can add a reaction with backed enum', function () {
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
});

it('can has a reaction with backed enum', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(Reaction::has($article, $user, ReactionType::Like))->toBeFalse();

    Reaction::add($article, $user, ReactionType::Like);
    expect(Reaction::has($article, $user, ReactionType::Like))->toBeTrue();
    expect(Reaction::has($article, $user, ReactionType::Love))->toBeFalse();
});

it('can remove a reaction with backed enum', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();
    $table = (new Reaction)->getTable();

    Reaction::add($article, $user, ReactionType::Like);
    $this->assertDatabaseCount($table, 1);

    Reaction::remove($article, $user, ReactionType::Like);
    $this->assertDatabaseCount($table, 0);
});

it('can toggle a reaction with backed enum', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();
    $table = (new Reaction)->getTable();

    Reaction::toggle($article, $user, ReactionType::Like);
    $this->assertDatabaseCount($table, 1);
    expect(Reaction::has($article, $user, ReactionType::Like))->toBeTrue();

    Reaction::toggle($article, $user, ReactionType::Like);
    $this->assertDatabaseCount($table, 0);
    expect(Reaction::has($article, $user, ReactionType::Like))->toBeFalse();
});

it('can count reactions with backed enum', function () {
    $article = Article::factory()->create();
    $users = User::factory(2)->create();

    expect(Reaction::count($article, ReactionType::Like))->toEqual(0);

    Reaction::add($article, $users[0], ReactionType::Like);
    Reaction::add($article, $users[1], ReactionType::Like);
    expect(Reaction::count($article, ReactionType::Like))->toEqual(2);
    expect(Reaction::count($article, ReactionType::Love))->toEqual(0);
});

it('can use enum class as allowed values', function () {
    config()->set('markable.allowed_values.reaction', ReactionType::class);

    $article = Article::factory()->create();
    $user = User::factory()->create();
    $table = (new Reaction)->getTable();

    Reaction::add($article, $user, ReactionType::Like);
    $this->assertDatabaseCount($table, 1);
    $this->assertDatabaseHas($table, [
        'value' => ReactionType::Like->value,
    ]);
});

it('cannot add invalid value when allowed values is enum class', function () {
    config()->set('markable.allowed_values.reaction', ReactionType::class);

    $article = Article::factory()->create();
    $user = User::factory()->create();

    expect(fn () => Reaction::add($article, $user, 'not_a_valid_enum_value'))->toThrow(InvalidMarkValueException::class);
});

it('can toggle a reaction', function () {
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
});
