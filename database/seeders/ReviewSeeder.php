<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $reviews = [
            '9784101010014' => [ // 吾輩は猫である
                ['user' => 0, 'rating' => 5, 'comment' => '猫の視点から人間社会を風刺する筆致が見事でした。'],
                ['user' => 1, 'rating' => 4, 'comment' => '古い作品ですが、今読んでも十分に面白いです。'],
                ['user' => 2, 'rating' => 4, 'comment' => '独特の語り口に最初は戸惑いましたが、慣れると癖になります。'],
            ],
            '9784422100524' => [ // 人を動かす
                ['user' => 1, 'rating' => 5, 'comment' => '人間関係の本質を突いた名著だと感じました。'],
                ['user' => 2, 'rating' => 4, 'comment' => '具体的な事例が多く、実践しやすい内容です。'],
                ['user' => 3, 'rating' => 5, 'comment' => '何度も読み返したくなる一冊です。'],
            ],
            '9784873115658' => [ // リーダブルコード
                ['user' => 0, 'rating' => 5, 'comment' => 'エンジニアなら一度は読むべき内容だと思います。'],
                ['user' => 3, 'rating' => 4, 'comment' => 'コードの命名規則について改めて考えさせられました。'],
                ['user' => 4, 'rating' => 5, 'comment' => 'チームでの開発にも役立つ知見が満載でした。'],
            ],
            '9784863940246' => [ // 7つの習慣
                ['user' => 0, 'rating' => 4, 'comment' => '内容が濃く、少しずつ読み進めました。'],
                ['user' => 2, 'rating' => 5, 'comment' => '自己成長のバイブルとして手元に置いています。'],
                ['user' => 4, 'rating' => 3, 'comment' => 'ボリュームが多く、読み切るのに時間がかかりました。'],
            ],
            '9784101010021' => [ // 坊っちゃん
                ['user' => 1, 'rating' => 4, 'comment' => 'テンポの良い展開で一気に読めました。'],
                ['user' => 3, 'rating' => 5, 'comment' => '主人公の江戸っ子気質が痛快でした。'],
                ['user' => 4, 'rating' => 4, 'comment' => '学生時代を思い出しながら読みました。'],
            ],
            '9784309226712' => [ // サピエンス全史
                ['user' => 0, 'rating' => 5, 'comment' => '人類の歴史を壮大なスケールで描いていて圧倒されました。'],
                ['user' => 2, 'rating' => 5, 'comment' => '知的好奇心を刺激される内容でした。'],
                ['user' => 3, 'rating' => 4, 'comment' => 'やや専門的な部分もありますが、読み応えがあります。'],
            ],
            '9784048930598' => [ // Clean Code
                ['user' => 1, 'rating' => 4, 'comment' => '実践的なサンプルコードが参考になりました。'],
                ['user' => 3, 'rating' => 5, 'comment' => 'コードレビューの視点が変わりました。'],
                ['user' => 4, 'rating' => 4, 'comment' => '初心者にはやや難しい部分もあると感じました。'],
            ],
            '9784478025819' => [ // 嫌われる勇気
                ['user' => 0, 'rating' => 5, 'comment' => '対話形式で読みやすく、内容も深かったです。'],
                ['user' => 2, 'rating' => 4, 'comment' => '考え方が大きく変わるきっかけになりました。'],
                ['user' => 4, 'rating' => 5, 'comment' => '何度も読み返している一冊です。'],
            ],
            '9784163902302' => [ // 火花
                ['user' => 1, 'rating' => 4, 'comment' => '芸人の視点から描かれる人間模様が印象的でした。'],
                ['user' => 3, 'rating' => 3, 'comment' => '静かな展開ですが、じわじわと心に残ります。'],
            ],
            '9784822289607' => [ // FACTFULNESS
                ['user' => 0, 'rating' => 5, 'comment' => '思い込みを覆されるデータが多く、驚きの連続でした。'],
                ['user' => 2, 'rating' => 4, 'comment' => 'ニュースの見方が変わる一冊でした。'],
            ],
            '9784822251468' => [ // コンテナ物語
                ['user' => 1, 'rating' => 4, 'comment' => '物流の仕組みがコンテナで一変した歴史がよく分かりました。'],
                ['user' => 2, 'rating' => 3, 'comment' => '専門的な内容ですが、興味深く読めました。'],
                ['user' => 3, 'rating' => 5, 'comment' => '普段意識しない物流の裏側を知れて面白かったです。'],
                ['user' => 4, 'rating' => 4, 'comment' => '経済に興味がある方におすすめです。'],
            ],
        ];

        foreach ($reviews as $isbn => $reviewList) {
            $book = Book::where('isbn', $isbn)->first();

            foreach ($reviewList as $data) {
                Review::create([
                    'user_id' => $users[$data['user']]->id,
                    'book_id' => $book->id,
                    'rating' => $data['rating'],
                    'comment' => $data['comment'],
                ]);
            }
        }
    }
}
