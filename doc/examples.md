# PHook使用例

このドキュメントでは、PHook拡張機能の様々な一般的なユースケースの使用例を提供します。

## 基本的なフック

### 関数のフック

```php
<?php
// シンプルな関数をフック
PHook\hook(
    null,                             // クラスなし（グローバル関数）
    'array_sum',                      // フックする関数
    function($instance, array $params) {
        echo "array_sum実行前、パラメータ: ";
        print_r($params);
        return null;                  // 元のパラメータを保持
    },
    function($instance, array $params, $returnValue) {
        echo "array_sumの戻り値: $returnValue\n";
        return $returnValue;          // 元の戻り値を保持
    }
);

// 関数を呼び出す
$result = array_sum([1, 2, 3, 4, 5]);
echo "最終結果: $result\n";
```

### メソッドのフック

```php
<?php
// クラスのメソッドをフック
PHook\hook(
    'DateTime',                       // クラス名
    'format',                         // フックするメソッド
    function($instance, array $params) {
        echo "DateTime->formatがフォーマット: " . $params[0] . " で呼び出されました\n";
        return null;
    },
    function($instance, array $params, $returnValue) {
        echo "DateTime->formatの戻り値: $returnValue\n";
        return $returnValue;
    }
);

// メソッドを呼び出す
$date = new DateTime();
echo $date->format('Y-m-d H:i:s') . "\n";
```

## パラメータの変更

### 位置によるパラメータの変更

```php
<?php
// 関数をフックしてパラメータを変更
PHook\hook(
    null,
    'strtoupper',
    function($instance, array $params) {
        echo "元の文字列: " . $params[0] . "\n";
        // 最初のパラメータを変更
        return [0 => "modified_" . $params[0]];
    }
);

// 関数を呼び出す
$result = strtoupper("hello");
echo "結果: $result\n";  // 出力: MODIFIED_HELLO
```

### 名前によるパラメータの変更

```php
<?php
// 名前付きパラメータを持つ関数を定義
function greeting($name, $greeting = "Hello") {
    echo "$greeting, $name!\n";
}

// 関数をフックして名前でパラメータを変更
PHook\hook(
    null,
    'greeting',
    function($instance, array $params) {
        // 'greeting'パラメータを変更
        return ['greeting' => 'Bonjour'];
    }
);

// 関数を呼び出す
greeting("John");  // 出力: Bonjour, John!
```

### 追加パラメータの追加

```php
<?php
// func_get_args()を使用する関数を定義
function flexible_function($required) {
    $args = func_get_args();
    echo "必須: $required\n";
    echo "全引数数: " . count($args) . "\n";
    print_r($args);
}

// パラメータを追加するフック
PHook\hook(
    null,
    'flexible_function',
    function($instance, array $params) {
        // さらに2つのパラメータを追加
        return [$params[0], 'extra1', 'extra2'];
    }
);

// 1つのパラメータだけで呼び出し
flexible_function("base");
// 出力: 3つのパラメータ: base, extra1, extra2
```

## 戻り値の変更

### シンプルな戻り値の変更

```php
<?php
// 戻り値を変更するフック
PHook\hook(
    null,
    'strlen',
    null,                             // 実行前フックなし
    function($instance, array $params, $returnValue) {
        echo "元の長さ: $returnValue\n";
        // 返された長さを2倍にする
        return $returnValue * 2;
    }
);

// 関数を呼び出す
$length = strlen("hello");
echo "変更された長さ: $length\n";    // 出力: 10 (5の代わりに)
```

### 複数の実行後フック

```php
<?php
// 最初のフック
PHook\hook(
    null,
    'strtolower',
    null,
    function($instance, array $params, $returnValue) {
        // 戻り値にテキストを追加
        return $returnValue . "_modified1";
    }
);

// 2番目のフック
PHook\hook(
    null,
    'strtolower',
    null,
    function($instance, array $params, $returnValue) {
        // 戻り値にさらにテキストを追加
        return $returnValue . "_modified2";
    }
);

// 関数を呼び出す
$result = strtolower("HELLO");
echo $result . "\n";  // 出力: hello_modified1_modified2
```

## 属性の使用

### Hook属性の使用

```php
<?php
// デフォルトフックハンドラの実装
class DefaultHandler {
    public static function pre($instance, array $params, ?string $class, string $function, 
                             ?string $filename, ?int $lineno, ?array $args = null) {
        echo "$filenameの$lineno行目の$functionの実行前フック\n";
        return null;
    }
    
    public static function post($instance, array $params, $returnValue) {
        echo "関数の戻り値: " . var_export($returnValue, true) . "\n";
        return $returnValue;
    }
}

// デフォルトハンドラを設定
ini_set('phook.attr_pre_handler_function', 'DefaultHandler::pre');
ini_set('phook.attr_post_handler_function', 'DefaultHandler::post');

// 関数を#[Hook]属性でマーク
#[Hook]
function sample_function($param) {
    echo "$paramでsample_functionを実行中\n";
    return $param * 2;
}

// 関数を呼び出す - フックは自動的に適用される
$result = sample_function(42);
echo "結果: $result\n";
```

### CaptureParam属性の使用

```php
<?php
// パラメータキャプチャを使用するカスタムハンドラ
class ParamLogger {
    public static function pre($instance, array $params, ?string $class, string $function, 
                             ?string $filename, ?int $lineno, ?array $args = null) {
        echo "$functionがキャプチャされたパラメータで呼び出されました:\n";
        print_r($args);
        return null;
    }
    
    public static function post($instance, array $params, $returnValue) {
        return $returnValue;
    }
}

// ハンドラを設定
ini_set('phook.attr_pre_handler_function', 'ParamLogger::pre');

// パラメータキャプチャを持つ関数
function process_user(
    #[CaptureParam] string $username,
    #[CaptureParam("email_address")] string $email,
    string $uncaptured
) {
    echo "ユーザー $username ($email) を処理中\n";
    return true;
}

// usernameとemailパラメータを自動的にキャプチャ
process_user("johndoe", "john@example.com", "その他のデータ");
```

## 高度なユースケース

### 例外処理

```php
<?php
// 例外を処理するフック
PHook\hook(
    null,
    'risky_function',
    null,
    function($instance, array $params, $returnValue, ?Throwable $exception) {
        if ($exception) {
            echo "例外をキャッチしました: " . $exception->getMessage() . "\n";
            // フォールバック値を返す
            return "fallback_value";
        }
        return $returnValue;
    }
);

function risky_function() {
    throw new Exception("何か問題が発生しました!");
    return "ここには到達しません";
}

// 関数を呼び出す - エラーは実行後フックで処理される
$result = risky_function();
echo "結果: $result\n";  // 出力: 結果: fallback_value
```

### メソッド呼び出しの傍受

```php
<?php
// PDOのすべてのメソッドをフック
PHook\hook(
    'PDO',
    'query',
    function($instance, array $params) {
        $sql = $params[0];
        echo "クエリを実行中: $sql\n";
        
        // プロファイリング用にクエリをログに記録
        $startTime = microtime(true);
        
        // 実行後フック用に開始時間を保存
        return ['__start_time' => $startTime];
    },
    function($instance, array $params, $returnValue, ?Throwable $exception, $hookData) {
        $endTime = microtime(true);
        $duration = ($endTime - $hookData['__start_time']) * 1000;
        
        echo "クエリ実行に " . number_format($duration, 2) . " ミリ秒かかりました\n";
        
        return $returnValue;
    }
);

// PDOインスタンスを作成してクエリを実行
$pdo = new PDO('sqlite::memory:');
$result = $pdo->query('SELECT 1+1');
```

### 関数呼び出しの監視

```php
<?php
// シンプルな関数呼び出しカウンター
class FunctionStats {
    public static $counts = [];
    
    public static function increment($function) {
        if (!isset(self::$counts[$function])) {
            self::$counts[$function] = 0;
        }
        self::$counts[$function]++;
    }
    
    public static function report() {
        foreach (self::$counts as $function => $count) {
            echo "$functionは$count回呼び出されました\n";
        }
    }
}

// 監視用に複数の関数をフック
$functions = ['strlen', 'strtoupper', 'strtolower'];
foreach ($functions as $function) {
    PHook\hook(
        null,
        $function,
        function($instance, array $params) use ($function) {
            FunctionStats::increment($function);
            return null;
        }
    );
}

// 関数を呼び出す
strlen("test");
strtoupper("hello");
strtolower("WORLD");
strtoupper("again");

// 統計を報告
FunctionStats::report();
```

### アスペクトの実装

```php
<?php
// シンプルなロギングアスペクト
class LoggingAspect {
    public static function beforeMethod($instance, array $params, ?string $class, string $method) {
        $className = $class ?? 'global';
        echo "[$className::$method] メソッドに入ります\n";
        return null;
    }
    
    public static function afterMethod($instance, array $params, $returnValue) {
        echo "メソッドが戻り値を返します: " . var_export($returnValue, true) . "\n";
        return $returnValue;
    }
    
    // このアスペクトをクラスメソッドに適用
    public static function apply($class, $method) {
        PHook\hook(
            $class,
            $method,
            [self::class, 'beforeMethod'],
            [self::class, 'afterMethod']
        );
    }
}

// クラスを定義
class MyService {
    public function getData($id) {
        echo "ID $id のデータを取得中\n";
        return ["id" => $id, "data" => "サンプル"];
    }
}

// アスペクトを適用
LoggingAspect::apply('MyService', 'getData');

// メソッドを呼び出す
$service = new MyService();
$data = $service->getData(123);
```