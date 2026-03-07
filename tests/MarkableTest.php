<?php

use Maize\Markable\Exceptions\InvalidMarkInstanceException;
use Maize\Markable\Markable;
use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\ModelWithInvalidMark;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

it('cannot register an invalid mark in markable model', function () {
    expect(fn () => new ModelWithInvalidMark)->toThrow(InvalidMarkInstanceException::class);
});

it('can register a mark in markable model', function () {
    $markable = new Article;

    expect($markable)->toBeInstanceOf(Article::class);
    expect(trait_uses_recursive($markable))->toContain(Markable::class);
});

it('can filter marked models', function () {
    $articles = Article::factory(5)->create();
    $posts = Post::factory(2)->create();
    $users = User::factory(2)->create();

    expect(Article::whereHasMark(app(Like::class), $users[0])->get())->toHaveCount(0);
    expect(Article::whereHasMark(app(Like::class), $users[1])->get())->toHaveCount(0);

    Like::add($articles[0], $users[0]);
    expect(Article::whereHasMark(app(Like::class), $users[0])->get())->toHaveCount(1);
    expect(Article::whereHasMark(app(Like::class), $users[1])->get())->toHaveCount(0);

    Like::add($articles[2], $users[0]);
    expect(Article::whereHasMark(app(Like::class), $users[0])->get())->toHaveCount(2);
    expect(Article::whereHasMark(app(Like::class), $users[1])->get())->toHaveCount(0);

    Like::add($articles[2], $users[1]);
    expect(Article::whereHasMark(app(Like::class), $users[0])->get())->toHaveCount(2);
    expect(Article::whereHasMark(app(Like::class), $users[1])->get())->toHaveCount(1);

    Like::add($posts[0], $users[0]);
    expect(Article::whereHasMark(app(Like::class), $users[0])->get())->toHaveCount(2);
    expect(Article::whereHasMark(app(Like::class), $users[1])->get())->toHaveCount(1);
});

it('should delete marks related to deleted markables', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

    $mark = Like::add($article, $user);

    $this->assertDatabaseHas($mark->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
    ]);

    $article->delete();

    $this->assertDatabaseMissing($mark->getTable(), [
        'user_id' => $user->getKey(),
        'markable_id' => $article->getKey(),
        'markable_type' => $article->getMorphClass(),
    ]);
});

it('should retrieve the list of users who marked an entity', function () {
    $article = Article::factory()->create();
    $users = User::factory(2)->create();

    Like::add($article, $users[0]);
    Like::add($article, $users[1]);

    expect($article->likers->modelKeys())->toEqual([
        $users[0]->getKey(),
        $users[1]->getKey(),
    ]);
});

it('should retrieve the list of marks of an entity', function () {
    $article = Article::factory()->create();
    $users = User::factory(2)->create();

    $like1 = Like::add($article, $users[0]);
    $like2 = Like::add($article, $users[1]);

    expect($article->likes->modelKeys())->toEqual([
        $like1->getKey(),
        $like2->getKey(),
    ]);
});
