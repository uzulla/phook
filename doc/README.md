# PHookドキュメント

このディレクトリには、OpenTelemetry PHP拡張機能から派生し、特に関数/メソッドフック機能に焦点を当てたPHook拡張機能の包括的なドキュメントが含まれています。

## ドキュメント内容

### コアドキュメント

- [概要](overview.md.ja) - PHook拡張機能とその機能の紹介
- [APIリファレンス](api-reference.md.ja) - すべての関数、属性、設定オプションの詳細なリファレンス
- [コード構造](code-structure.md.ja) - 拡張機能の内部コード構造の概要
- [使用例](examples.md.ja) - PHookの実用的な使用例
- [実装ガイド](implementation-guide.md.ja) - OpenTelemetry拡張機能をPHookに変換するためのガイド
- [移行ガイド](migration-guide.md.ja) - OpenTelemetry拡張機能からPHookへの移行のためのユーザーガイド

## PHookについて

PHookは、強力な関数/メソッド傍受メカニズムを提供するPHP拡張機能です。この拡張機能により、以下のことが可能になります:

- 任意のPHP関数やメソッドに実行前および実行後のフックを登録
- 実行前に関数パラメータを変更
- 実行後に戻り値を変更
- 宣言的なフック用のPHP 8属性を使用
- 関数パラメータのキャプチャと操作

この拡張機能はOpenTelemetry PHP計装拡張機能に基づいていますが、OpenTelemetry固有の機能を削除し、関数フック機能のみに焦点を当てています。

## クイックスタート

```php
<?php
// グローバル関数をフック
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
        return $returnValue * 2;      // 戻り値を2倍にする
    }
);

// 関数を呼び出す
$result = array_sum([1, 2, 3, 4, 5]);
echo "最終結果: $result\n";       // 30を出力 (15 * 2)
```

## PHP 8属性サポート

```php
<?php
// 属性ハンドラーを設定
ini_set('phook.attr_pre_handler_function', 'MyHandler::pre');
ini_set('phook.attr_post_handler_function', 'MyHandler::post');

// フック属性を持つ関数
#[Hook]
function process_data($data) {
    return $data * 2;
}

// パラメータキャプチャを持つ関数
function user_login(
    #[CaptureParam] string $username,
    #[CaptureParam("user_pass")] string $password
) {
    // 関数本体
}
```

## 関連リソース

- [元の拡張機能のGitHubリポジトリ](https://github.com/open-telemetry/opentelemetry-php-instrumentation)
- [PHP拡張機能開発ドキュメント](https://www.php.net/manual/en/internals2.structure.php)
- [PHP 8オブザーバーAPIドキュメント](https://www.php.net/manual/en/zend.opcode-handler-hook.php)