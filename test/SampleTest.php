
<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;

class SampleTest extends TestCase
{
    protected $pdo; // PDOオブジェクト用のプロパティ(メンバ変数)の宣言
    protected $driver;

    public function setUp(): void
    {
        // PDOオブジェクトを生成し、データベースに接続
        $dsn = "mysql:host=db;dbname=shop;charset=utf8";
        $user = "shopping";
        $password = "site";
        try {
            $this->pdo = new PDO($dsn, $user, $password);
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage();
            die();
        }

        #XAMPP環境で実施している場合、$dsn設定を変更する必要がある
        //ファイルパス
        $rdfile = __DIR__ . '/../src/classes/dbdata.php';
        $val = "host=db;";

        //ファイルの内容を全て文字列に読み込む
        $str = file_get_contents($rdfile);
        //検索文字列に一致したすべての文字列を置換する
        $str = str_replace("host=localhost;", $val, $str);
        //文字列をファイルに書き込む
        file_put_contents($rdfile, $str);

        // chrome ドライバーの起動
        $host = 'http://172.17.0.1:4444/wd/hub'; #Github Actions上で実行可能なHost
        // chrome ドライバーの起動
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    public function testProductSelect()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/index.php');

        // トップページ画面のpcリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[4]->click();

        // ジャンル別商品一覧画面のtdタグを取得
        $element_td = $this->driver->findElements(WebDriverBy::tagName('td'));

        //データベースの値を取得
        $sql = 'select * from items where genre = ?';  // SQL文の定義
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pc']);
        $items = $stmt->fetchAll();
        $i = 0;

        // assert
        foreach ($items as $item) {
            $this->assertEquals($item['name'], $element_td[($i * 5) + 1]->getText(), 'ジャンル別商品一覧画面の処理に誤りがあります。');
            $i++;
        }
    }

    public function testProductDetail()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/index.php');

        // トップページ画面のpcリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[4]->click();

        // ジャンル別商品一覧画面の詳細リンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[5]->click();

        // ジャンル別商品一覧画面のtdタグを取得
        $element_td = $this->driver->findElements(WebDriverBy::tagName('td'));

        //データベースの値を取得
        $sql = 'select * from items where ident = ?';       // SQL文の定義
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['1']);
        $item = $stmt->fetch();

        // assert
        $this->assertEquals($item['name'], $element_td[0]->getText(), '商品詳細画面の処理に誤りがあります。');
    }
}
