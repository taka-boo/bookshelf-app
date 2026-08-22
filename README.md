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

| 技術                  | バージョン                |
| --------------------- | ------------------------- |
| PHP                   | 8.5                       |
| Laravel               | 10.x                      |
| MySQL                 | 8.4                       |
| Docker / Laravel Sail | -                         |
| Vite / Tailwind CSS   | -                         |
| Laravel Fortify       | 認証                      |
| Laravel Sanctum       | APIトークン認証           |
| Google Books API      | ISBN検索                  |
| PHPUnit               | テスト（カバレッジ96.8%） |

## 作成者

綾部 貴之

## APIエンドポイント一覧

### 読み取り系（認証不要）

| メソッド | パス                 | 概要                                             |
| -------- | -------------------- | ------------------------------------------------ |
| GET      | /api/v1/books        | 書籍一覧（検索・フィルタ・ページネーション対応） |
| GET      | /api/v1/books/{book} | 書籍詳細（ジャンル・レビュー含む）               |

### 書き込み系（Sanctumトークン認証必須）

| メソッド | パス                 | 概要                   |
| -------- | -------------------- | ---------------------- |
| POST     | /api/v1/books        | 書籍登録               |
| PUT      | /api/v1/books/{book} | 書籍更新（所有者のみ） |
| DELETE   | /api/v1/books/{book} | 書籍削除（所有者のみ） |

## テスト

```bash
sail artisan test
sail artisan test --coverage
```

## 開発環境URL

http://localhost/books
