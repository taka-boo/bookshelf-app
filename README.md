# プロジェクト名：BookShelf 書籍レビューアプリ

## 概要

書籍の登録・レビュー投稿・お気に入り管理・ランキング表示ができるWebアプリケーションです。
ジャンルによる分類、レビューへのいいね機能、読書計画機能、リマインダー通知機能を備えています。
外部アプリケーション向けの公開API（JSON）も提供しています。

## ER図

![ER図](bookshelf-app-er.drawio.png)

## 環境構築手順

### 1. リポジトリをクローン

```bash
git clone <リポジトリURL>
cd bookshelf-app
```

### 2. 環境変数を設定

cp .env.example .env
.envファイルに以下を追加してください。
GOOGLE_BOOKS_API_KEY=あなたのAPIキー
Google Books APIキーは Google Cloud Console でプロジェクトを作成し、Books APIを有効化した上で発行してください。

※ .env.example は以下の値に修正済みです（Docker環境でMySQLに接続するための設定）。
・DB_HOST=mysql（コンテナ名で接続するため）
・DB_USERNAME=sail（rootはMySQL公式イメージで使用不可のため）
・DB_PASSWORD=password（空のままだとテスト用DB作成が失敗するため）

### 3. パッケージのインストール

クローン直後は `vendor/` が存在せず `./vendor/bin/sail` が使えないため、まずComposerの一時コンテナで依存パッケージをインストールします。

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

※ アプリ本体はPHP 8.5（`compose.yaml`のSailランタイム）で動作しますが、上記のComposer一時コンテナはPHP 8.5版が現時点で提供されていないため、直近の`php84-composer`イメージを使用しています（composer.jsonの要件は`^8.1`のため依存解決には影響ありません。バージョン不一致チェックは`--ignore-platform-reqs`で回避しています）。

### 4. Dockerコンテナを起動

```bash
./vendor/bin/sail up -d
sail npm install
```

### 5. アプリケーションキーの生成

```bash
sail artisan key:generate
```

### 6. データベースの作成とマイグレーション

```bash
sail artisan migrate --seed
```

### 7. テスト用データベースの作成

```bash
sail mysql
```

`mysql>` が出たら以下を実行してください。

```sql
CREATE DATABASE IF NOT EXISTS testing;
exit
```

### 8. 開発サーバーの起動

```bash
sail npm run dev
```

### 9. アプリケーションにアクセス

ブラウザで http://localhost/books にアクセスしてください。

## 使用技術

| 技術                  | バージョン                 |
| --------------------- | -------------------------- |
| PHP                   | 8.5                        |
| Laravel               | 10.x                       |
| MySQL                 | 8.4                        |
| Docker / Laravel Sail | -                          |
| Vite / Tailwind CSS   | -                          |
| Laravel Fortify       | 認証                       |
| Laravel Sanctum       | APIトークン認証            |
| Google Books API      | ISBN検索                   |
| PHPUnit               | テスト（カバレッジ97.0% ） |

## 作成者

綾部 貴之

## APIエンドポイント一覧

### 共通事項

- ベースURL: `http://localhost/api/v1`（本番相当のURLに読み替えてください）
- レスポンス形式: JSON
- 認証方式: 書き込み系エンドポイント（POST/PUT/DELETE）は [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum) のトークン認証が必須です。取得したトークンを`Authorization`ヘッダーに付与してください。

  ```
  Authorization: Bearer {トークン}
  ```

  読み取り系エンドポイント（GET）は認証不要です。

| メソッド | パス                 | 概要                                             | 認証                     |
| -------- | -------------------- | ------------------------------------------------ | ------------------------ |
| GET      | /api/v1/books        | 書籍一覧（検索・フィルタ・ページネーション対応） | 不要                     |
| GET      | /api/v1/books/{book} | 書籍詳細（ジャンル・レビュー含む）               | 不要                     |
| POST     | /api/v1/books        | 書籍登録                                         | 必須（Sanctum）          |
| PUT      | /api/v1/books/{book} | 書籍更新                                         | 必須（Sanctum・所有者のみ） |
| DELETE   | /api/v1/books/{book} | 書籍削除                                         | 必須（Sanctum・所有者のみ） |

以下、各エンドポイントの詳細です。掲載しているリクエスト・レスポンス例は、実際に稼働環境へcurlでリクエストを送信して取得した実物です（一部の値は差し替え済み）。

---

### GET /api/v1/books（書籍一覧）

認証不要。以下のクエリパラメータに対応しています（`app/Http/Requests/Api/V1/IndexBookRequest.php`）。

| パラメータ | 型      | 必須 | 説明                                       |
| ---------- | ------- | ---- | ------------------------------------------ |
| keyword    | string  | 任意 | タイトル・著者名を部分一致検索（255文字以内） |
| genre_id   | integer | 任意 | 指定したジャンルIDで絞り込み               |
| page       | integer | 任意 | ページ番号（1以上、デフォルト1）           |
| per_page   | integer | 任意 | 1ページあたりの件数（1〜100、デフォルト10） |

リクエスト例:

```
GET /api/v1/books?keyword=猫&genre_id=1&per_page=2&page=1
```

レスポンス例（`200 OK`）:

```json
{
  "data": [
    {
      "id": 1,
      "title": "吾輩は猫である",
      "author": "夏目漱石",
      "isbn": "9784101010014",
      "published_date": "1905-01-01T00:00:00.000000Z",
      "description": "吾輩は猫であるは夏目漱石による作品です。",
      "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=1",
      "genres": [
        { "id": 1, "name": "小説" }
      ],
      "reviews_avg_rating": 4.3,
      "reviews_count": 3
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/books?page=1",
    "last": "http://localhost/api/v1/books?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 2,
    "to": 1,
    "total": 1
  }
}
```

各書籍にジャンル一覧(`genres`)・平均評価(`reviews_avg_rating`、レビュー無しは`null`)・レビュー件数(`reviews_count`)が含まれます。一覧APIではレビュー本文一覧(`reviews`)は含まれません（書籍詳細APIでのみ返却）。`sort`パラメータには対応していません（Web版のみの機能です）。

---

### GET /api/v1/books/{book}（書籍詳細）

認証不要。`{book}`は書籍の`id`（数値）です。

リクエスト例:

```
GET /api/v1/books/1
```

レスポンス例（`200 OK`、ジャンル・レビュー一覧を含む）:

```json
{
  "data": {
    "id": 1,
    "title": "吾輩は猫である",
    "author": "夏目漱石",
    "isbn": "9784101010014",
    "published_date": "1905-01-01T00:00:00.000000Z",
    "description": "吾輩は猫であるは夏目漱石による作品です。",
    "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=1",
    "genres": [
      { "id": 1, "name": "小説" }
    ],
    "reviews": [
      {
        "id": 1,
        "rating": 5,
        "comment": "猫の視点から人間社会を風刺する筆致が見事でした。",
        "user_name": "山田太郎",
        "created_at": "2026-08-12 11:03"
      },
      {
        "id": 2,
        "rating": 4,
        "comment": "古い作品ですが、今読んでも十分に面白いです。",
        "user_name": "鈴木花子",
        "created_at": "2026-08-12 11:03"
      }
    ],
    "reviews_avg_rating": 4.3,
    "reviews_count": 3
  }
}
```

存在しない`{book}`を指定した場合、`404 Not Found`で以下のJSONが返ります。

```json
{
  "error": "書籍が見つかりませんでした"
}
```

---

### POST /api/v1/books（書籍登録・Sanctum認証必須）

リクエスト例（認証ヘッダー付き）:

```bash
curl -X POST http://localhost/api/v1/books \
  -H "Authorization: Bearer {トークン}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "サンプル書籍",
    "author": "サンプル著者",
    "isbn": "9784999999999",
    "published_date": "2026-01-01",
    "description": "説明文です。",
    "image_url": "https://example.com/sample.jpg",
    "genres": [1]
  }'
```

リクエストボディ:

| 項目           | 必須/任意 | 型      | 説明                                   |
| -------------- | --------- | ------- | -------------------------------------- |
| title          | 必須      | string  | 書籍タイトル（255文字以内）            |
| author         | 必須      | string  | 著者名（255文字以内）                  |
| isbn           | 任意      | string  | ISBN（13桁の数字、一意制約あり）       |
| published_date | 任意      | date    | 出版日（`YYYY-MM-DD`）                 |
| description    | 任意      | string  | 説明（1000文字以内）                   |
| image_url      | 任意      | string  | 画像URL（URL形式）                     |
| genres         | 必須      | array   | ジャンルIDの配列（1件以上）            |

成功時のレスポンス例（`201 Created`）:

```json
{
  "data": {
    "id": 12,
    "title": "README検証用の本",
    "author": "検証太郎",
    "isbn": "9784999999999",
    "published_date": "2026-01-01T00:00:00.000000Z",
    "description": "README用の検証データです。",
    "image_url": "https://example.com/readme-test.jpg",
    "genres": [
      { "id": 1, "name": "小説" }
    ],
    "reviews": [],
    "reviews_avg_rating": null,
    "reviews_count": 0
  }
}
```

未認証の場合(`401 Unauthorized`):

```json
{ "message": "Unauthenticated." }
```

バリデーションエラー時のレスポンス例（`422 Unprocessable Entity`、日本語メッセージ）:

```json
{
  "message": "タイトルを入力してください。 (and 3 more errors)",
  "errors": {
    "title": ["タイトルを入力してください。"],
    "author": ["著者名を入力してください。"],
    "isbn": ["ISBNは13桁の数字で入力してください。"],
    "genres": ["ジャンルを1つ以上選択してください。"]
  }
}
```

---

### PUT /api/v1/books/{book}（書籍更新・Sanctum＋所有者のみ）

リクエストボディの項目・バリデーションはPOSTと同様です。ただし**ISBNの一意性チェックは更新対象の書籍自身を除外**するため、他の書籍と重複しない限り、自分自身の現在のISBNをそのまま送っても`422`にはなりません。

リクエスト例:

```bash
curl -X PUT http://localhost/api/v1/books/12 \
  -H "Authorization: Bearer {所有者のトークン}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "サンプル書籍(更新後)",
    "author": "サンプル著者",
    "isbn": "9784999999999",
    "published_date": "2026-01-01",
    "description": "更新しました。",
    "image_url": "https://example.com/sample.jpg",
    "genres": [1]
  }'
```

成功時のレスポンス例（`200 OK`）:

```json
{
  "data": {
    "id": 12,
    "title": "README検証用の本(更新後)",
    "author": "検証太郎",
    "isbn": "9784999999999",
    "published_date": "2026-01-01T00:00:00.000000Z",
    "description": "更新しました。",
    "image_url": "https://example.com/readme-test.jpg",
    "genres": [
      { "id": 1, "name": "小説" }
    ],
    "reviews": [],
    "reviews_avg_rating": null,
    "reviews_count": 0
  }
}
```

所有者以外が更新しようとした場合（`403 Forbidden`）:

```json
{ "message": "This action is unauthorized." }
```

---

### DELETE /api/v1/books/{book}（書籍削除・Sanctum＋所有者のみ）

リクエスト例:

```bash
curl -X DELETE http://localhost/api/v1/books/12 \
  -H "Authorization: Bearer {所有者のトークン}" \
  -H "Accept: application/json"
```

成功時（`204 No Content`、レスポンスボディなし）。削除に伴い、その書籍に紐づくレビュー・お気に入り・ジャンル紐付け（`reviews`・`favorites`・`book_genre`）も外部キーの`ON DELETE CASCADE`により自動的に削除されます。

所有者以外が削除しようとした場合（`403 Forbidden`）:

```json
{ "message": "This action is unauthorized." }
```

## テスト

```bash
sail artisan test
sail artisan test --coverage
```

## 開発環境URL

http://localhost/books
