# PHook APIリファレンス

## コア関数

### `PHook\hook(string|null $class, string $function, ?\Closure $pre = null, ?\Closure $post = null): bool`

PHP関数またはメソッドのフックを登録します。

#### パラメータ:

- `$class` (string|null): フックするメソッドを含むクラス名、またはグローバル関数の場合は`null`
- `$function` (string): フックする関数またはメソッドの名前
- `$pre` (?Closure): フックされた関数が実行される前に実行するコールバック関数
- `$post` (?Closure): フックされた関数が完了した後に実行するコールバック関数

#### 戻り値:

- `bool`: フックが正常に登録された場合は`true`、そうでない場合は`false`

#### 実行前フックのコールバックシグネチャ:

```php
function($instance, array $params, ?string $class, string $function, ?string $filename, ?int $lineno): array|null
```

- `$instance`: メソッド呼び出しの場合はオブジェクトインスタンス、関数呼び出しの場合は`null`
- `$params`: 関数に渡されたパラメータの配列
- `$class`: クラス名（該当する場合）
- `$function`: 関数名
- `$filename`: 関数が呼び出されたファイル名
- `$lineno`: 関数が呼び出された行番号

実行前フックコールバックは以下のことができます:
- 元のパラメータを変更せずに`null`を返す
- 位置によって特定のパラメータを置き換えるために数値キーを持つ連想配列を返す
- 名前によってパラメータを置き換えるために文字列キーを持つ連想配列を返す
- すべてのパラメータを置き換えるために順序配列を返す

#### 実行後フックのコールバックシグネチャ:

```php
function($instance, array $params, $returnValue, ?\Throwable $exception): mixed
```

- `$instance`: メソッド呼び出しの場合はオブジェクトインスタンス、関数呼び出しの場合は`null`
- `$params`: パラメータの配列（元の値、実行前フックによる修正ではない）
- `$returnValue`: フックされた関数によって返された値
- `$exception`: 関数によってスローされた例外、または例外がない場合は`null`

実行後フックコールバックは以下のことができます:
- 変更された戻り値を返す
- 実行後フックが値を返さない場合は、元の戻り値が使用される

#### 例:

```php
// グローバル関数をフック
PHook\hook(
    null,
    'array_sum',
    function($instance, array $params) {
        echo "array_sumの実行前、項目数: " . count($params[0]) . "\n";
        return null; // 元のパラメータを保持
    },
    function($instance, array $params, $returnValue) {
        echo "array_sumが返した値: " . $returnValue . "\n";
        return $returnValue * 2; // 戻り値を変更
    }
);

// メソッドをフック
PHook\hook(
    'PDO',
    'query',
    function($instance, array $params) {
        echo "SQLクエリ: " . $params[0] . "\n";
        return null;
    },
    function($instance, array $params, $returnValue) {
        echo "クエリが " . $returnValue->rowCount() . " 行を返しました\n";
        return $returnValue;
    }
);
```

## PHP属性

### `#[Hook]`

関数またはメソッドをデフォルトまたはカスタムハンドラーで自動的にフックするようにマークします。

#### パラメータ:

- `string $name = ""`: フックのオプションのカスタム名
- `array $attributes = []`: フックハンドラーに渡すオプションの属性

#### 例:

```php
#[Hook]
function process_data($data) {
    // この関数は自動的にフックされます
    return $data;
}

#[Hook(name: "custom_event", attributes: ["important" => true])]
function critical_operation() {
    // カスタムフック名と属性
}
```

### `#[CaptureParam]`

フック中にキャプチャするパラメータをマークします。

#### パラメータ:

- `string $name = ""`: キャプチャされたパラメータのオプションのカスタム名

#### 例:

```php
function process_user(
    #[CaptureParam] string $username,
    #[CaptureParam("user_email")] string $email
) {
    // $usernameと$emailパラメータがキャプチャされます
}
```

## 設定オプション

PHookを設定するための以下のINI設定が利用可能です:

| 設定                       | デフォルト | 説明                                        |
|-------------------------------|------------|----------------------------------------------------|
| `phook.enabled`               | `1`        | 拡張機能の有効/無効                     |
| `phook.validate_hook_functions` | `1`      | フックコールバックのシグネチャを検証                   |
| `phook.display_warnings`      | `1`        | フック関連の問題に関する警告を表示            |
| `phook.allow_stack_extension` | `1`        | 関数定義を超えた追加パラメータの追加を許可 |
| `phook.attr_hooks_enabled`    | `1`        | 属性ベースのフック(#[Hook])を有効にする           |
| `phook.attr_pre_handler_function` | `"PHook\\DefaultHandler::pre"` | 属性のデフォルト実行前フックハンドラー |
| `phook.attr_post_handler_function` | `"PHook\\DefaultHandler::post"` | 属性のデフォルト実行後フックハンドラー |

#### 例:

```ini
; php.ini
phook.enabled = 1
phook.attr_hooks_enabled = 1
phook.attr_pre_handler_function = "MyApp\\Hooks::preHandler"
phook.attr_post_handler_function = "MyApp\\Hooks::postHandler"
```