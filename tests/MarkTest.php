<?php

use Maize\Markable\Exceptions\InvalidMarkableInstanceException;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Models\Favorite;
use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

it('can add a mark', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    Like::add($article, $user);

    $this->assertDatabaseHas((new Like)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
        'value' => null,
    ]);
});

it('can add metadata', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    Favorite::add($article, $user, null, [
        'test_data' => true,
    ]);

    $this->assertDatabaseHas((new Favorite)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
        'value' => null,
        'metadata' => json_encode(['test_data' => true]),
    ]);

    Like::toggle($article, $user, null, [
        'test_data' => true,
    ]);

    $this->assertDatabaseHas((new Like)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
        'value' => null,
        'metadata' => json_encode(['test_data' => true]),
    ]);
});

it('cannot remove with an invalid markable type', function () {
    $user = User::factory()->create();

    expect(fn () => Like::remove($user, $user))->toThrow(InvalidMarkableInstanceException::class);
});

it('cannot remove an unregistered mark', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    expect(fn () => Bookmark::remove($post, $user))->toThrow(InvalidMarkableInstanceException::class);
});

it('can remove a mark', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    Like::add($article, $user);
    Like::remove($article, $user);

    $this->assertDatabaseMissing((new Like)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
    ]);
});

it('can count marks', function () {
    $articles = Article::factory(2)->create();
    $post = Post::factory()->create();
    $users = User::factory(2)->create();

    expect(Like::count($articles[0]))->toEqual(0);
    expect(Like::count($articles[1]))->toEqual(0);
    expect(Like::count($post))->toEqual(0);

    Like::add($articles[0], $users[0]);
    expect(Like::count($articles[0]))->toEqual(1);
    expect(Like::count($articles[1]))->toEqual(0);
    expect(Like::count($post))->toEqual(0);

    Like::add($articles[0], $users[0]);
    expect(Like::count($articles[0]))->toEqual(1);
    expect(Like::count($articles[1]))->toEqual(0);
    expect(Like::count($post))->toEqual(0);

    Like::add($articles[0], $users[1]);
    expect(Like::count($articles[0]))->toEqual(2);
    expect(Like::count($articles[1]))->toEqual(0);
    expect(Like::count($post))->toEqual(0);

    Like::add($articles[1], $users[0]);
    expect(Like::count($articles[0]))->toEqual(2);
    expect(Like::count($articles[1]))->toEqual(1);
    expect(Like::count($post))->toEqual(0);

    Like::add($post, $users[0]);
    expect(Like::count($articles[0]))->toEqual(2);
    expect(Like::count($articles[1]))->toEqual(1);
    expect(Like::count($post))->toEqual(1);
});

it('can check if user has mark', function () {
    $articles = Article::factory(2)->create();
    $post = Post::factory()->create();
    $users = User::factory(2)->create();

    expect(Like::has($articles[0], $users[0]))->toBeFalse();
    expect(Like::has($articles[0], $users[1]))->toBeFalse();
    expect(Like::has($articles[1], $users[0]))->toBeFalse();
    expect(Like::has($articles[1], $users[1]))->toBeFalse();

    Like::add($articles[0], $users[0]);
    expect(Like::has($articles[0], $users[0]))->toBeTrue();
    expect(Like::has($articles[0], $users[1]))->toBeFalse();
    expect(Like::has($articles[1], $users[0]))->toBeFalse();
    expect(Like::has($articles[1], $users[1]))->toBeFalse();
});

it('can toggle a mark', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    Like::toggle($article, $user);

    $this->assertDatabaseHas((new Like)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
        'value' => null,
    ]);

    Like::toggle($article, $user);

    $this->assertDatabaseMissing((new Like)->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
    ]);
});

it('can retrieve markable morph', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    $like = Like::add($article, $user);

    expect($like->markable)->toBeInstanceOf(Article::class);
    expect($like->markable->getKey())->toEqual($article->getKey());
});

it('can retrieve user relation', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    $like = Like::add($article, $user);

    expect($like->user)->toBeInstanceOf(User::class);
    expect($like->user->getKey())->toEqual($user->getKey());
});
