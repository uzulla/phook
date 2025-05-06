# PHook拡張機能の実装ガイド

このガイドでは、元のOpenTelemetry PHP拡張機能をPHook拡張機能に変換するための詳細な手順を提供します。OpenTelemetry固有の機能を削除しつつ、コアとなるフックメカニズムに焦点を当てています。

## 変更の概要

1. 拡張機能と名前空間の名前変更
2. OpenTelemetry固有のコードの削除
3. 属性とクラスの名前変更
4. 設定パラメータの更新
5. ドキュメントと例の更新

## 詳細な実装手順

### 1. 拡張機能の名前変更

#### 拡張機能名とバージョンの更新

ファイル: `php_opentelemetry.h` → `php_phook.h`

```c
// 旧
#define PHP_OPENTELEMETRY_VERSION "1.1.2"
extern zend_module_entry opentelemetry_module_entry;
#define phpext_opentelemetry_ptr &opentelemetry_module_entry

// 新 
#define PHP_PHOOK_VERSION "1.0.0"
extern zend_module_entry phook_module_entry;
#define phpext_phook_ptr &phook_module_entry
```

#### モジュールグローバル変数の更新

ファイル: `php_opentelemetry.h` → `php_phook.h`

```c
// 旧
ZEND_BEGIN_MODULE_GLOBALS(opentelemetry)
    // ... グローバル変数 ...
ZEND_END_MODULE_GLOBALS(opentelemetry)

ZEND_EXTERN_MODULE_GLOBALS(opentelemetry)
#define OTEL_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(opentelemetry, v)

// 新
ZEND_BEGIN_MODULE_GLOBALS(phook)
    // ... 同じグローバル変数 ...
ZEND_END_MODULE_GLOBALS(phook)

ZEND_EXTERN_MODULE_GLOBALS(phook)
#define PHOOK_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(phook, v)
```

#### 拡張機能登録の更新

ファイル: `opentelemetry.c` → `phook.c`

```c
// 旧
zend_module_entry opentelemetry_module_entry = {
    STANDARD_MODULE_HEADER,
    "opentelemetry",
    ext_functions,
    PHP_MINIT(opentelemetry),
    PHP_MSHUTDOWN(opentelemetry),
    PHP_RINIT(opentelemetry),
    PHP_RSHUTDOWN(opentelemetry),
    PHP_MINFO(opentelemetry),
    PHP_OPENTELEMETRY_VERSION,
    STANDARD_MODULE_PROPERTIES
};

// 新
zend_module_entry phook_module_entry = {
    STANDARD_MODULE_HEADER,
    "phook",
    ext_functions,
    PHP_MINIT(phook),
    PHP_MSHUTDOWN(phook),
    PHP_RINIT(phook),
    PHP_RSHUTDOWN(phook),
    PHP_MINFO(phook),
    PHP_PHOOK_VERSION,
    STANDARD_MODULE_PROPERTIES
};
```

### 2. 名前空間と関数名の変更

#### PHPスタブの更新

ファイル: `opentelemetry.stub.php` → `phook.stub.php`

```php
// 旧
namespace OpenTelemetry\Instrumentation;

function hook(
    string|null $class,
    string $function,
    ?\Closure $pre = null,
    ?\Closure $post = null,
): bool {}

// 新
namespace PHook;

function hook(
    string|null $class,
    string $function,
    ?\Closure $pre = null,
    ?\Closure $post = null,
): bool {}
```

#### 関数登録の更新

ファイル: `opentelemetry_arginfo.h` → `phook_arginfo.h`

更新されたスタブから自動生成された引数情報ファイルを更新します。

### 3. 属性名の変更

#### WithSpan属性の置き換え

```c
// 旧
static zend_string *withspan_attribute_name;
withspan_attribute_name = zend_string_init("OpenTelemetry\\API\\Instrumentation\\WithSpan", sizeof("OpenTelemetry\\API\\Instrumentation\\WithSpan") - 1, 1);

// 新
static zend_string *hook_attribute_name;
hook_attribute_name = zend_string_init("PHook\\Hook", sizeof("PHook\\Hook") - 1, 1);
```

#### SpanAttribute属性の置き換え

```c
// 旧
static zend_string *spanattribute_attribute_name;
spanattribute_attribute_name = zend_string_init("OpenTelemetry\\API\\Instrumentation\\SpanAttribute", sizeof("OpenTelemetry\\API\\Instrumentation\\SpanAttribute") - 1, 1);

// 新
static zend_string *captureparam_attribute_name;
captureparam_attribute_name = zend_string_init("PHook\\CaptureParam", sizeof("PHook\\CaptureParam") - 1, 1);
```

### 4. 設定パラメータの更新

#### INI設定の更新

ファイル: `opentelemetry.c` → `phook.c`

```c
// 旧
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("opentelemetry.validate_hook_functions", "1", PHP_INI_ALL, OnUpdateBool, validate_hook_functions, zend_opentelemetry_globals, opentelemetry_globals)
    STD_PHP_INI_ENTRY("opentelemetry.display_warnings", "1", PHP_INI_ALL, OnUpdateBool, display_warnings, zend_opentelemetry_globals, opentelemetry_globals)
    STD_PHP_INI_ENTRY("opentelemetry.allow_stack_extension", "1", PHP_INI_ALL, OnUpdateBool, allow_stack_extension, zend_opentelemetry_globals, opentelemetry_globals)
    STD_PHP_INI_ENTRY("opentelemetry.attr_hooks_enabled", "1", PHP_INI_ALL, OnUpdateBool, attr_hooks_enabled, zend_opentelemetry_globals, opentelemetry_globals)
    STD_PHP_INI_ENTRY("opentelemetry.attr_pre_handler_function", "OpenTelemetry\\API\\Instrumentation\\WithSpanHandler::pre", PHP_INI_ALL, OnUpdateString, pre_handler_function_fqn, zend_opentelemetry_globals, opentelemetry_globals)
    STD_PHP_INI_ENTRY("opentelemetry.attr_post_handler_function", "OpenTelemetry\\API\\Instrumentation\\WithSpanHandler::post", PHP_INI_ALL, OnUpdateString, post_handler_function_fqn, zend_opentelemetry_globals, opentelemetry_globals)
PHP_INI_END()

// 新
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("phook.validate_hook_functions", "1", PHP_INI_ALL, OnUpdateBool, validate_hook_functions, zend_phook_globals, phook_globals)
    STD_PHP_INI_ENTRY("phook.display_warnings", "1", PHP_INI_ALL, OnUpdateBool, display_warnings, zend_phook_globals, phook_globals)
    STD_PHP_INI_ENTRY("phook.allow_stack_extension", "1", PHP_INI_ALL, OnUpdateBool, allow_stack_extension, zend_phook_globals, phook_globals)
    STD_PHP_INI_ENTRY("phook.attr_hooks_enabled", "1", PHP_INI_ALL, OnUpdateBool, attr_hooks_enabled, zend_phook_globals, phook_globals)
    STD_PHP_INI_ENTRY("phook.attr_pre_handler_function", "PHook\\DefaultHandler::pre", PHP_INI_ALL, OnUpdateString, pre_handler_function_fqn, zend_phook_globals, phook_globals)
    STD_PHP_INI_ENTRY("phook.attr_post_handler_function", "PHook\\DefaultHandler::post", PHP_INI_ALL, OnUpdateString, post_handler_function_fqn, zend_phook_globals, phook_globals)
PHP_INI_END()
```

### 5. OpenTelemetry固有のコードの削除

#### スパン作成コードの削除

スパンを作成したり、スパン属性を追加したり、OpenTelemetry APIとやり取りするコードを検索して削除します。これには以下が含まれます:

- デフォルトの属性ハンドラーでスパンを作成するコード
- 関数パラメータからスパン属性を抽出するコード
- OpenTelemetryコレクターまたはエクスポーターとの統合コード

#### 一般的な関数名の変更

- `opentelemetry_observer_init()`を`phook_observer_init()`に変更
- 他の関数も適切に新しい拡張機能名に合わせて変更

### 6. ビルド設定の更新

#### config.m4の更新

ファイル: `config.m4`

```m4
# 旧
PHP_ARG_ENABLE([opentelemetry],
  [whether to enable opentelemetry support],
  [AS_HELP_STRING([--enable-opentelemetry],
    [Enable opentelemetry support])],
  [no])

# 新
PHP_ARG_ENABLE([phook],
  [whether to enable PHook function hooking support],
  [AS_HELP_STRING([--enable-phook],
    [Enable PHook function hooking support])],
  [no])
```

#### ファイルリストの更新

```m4
# 旧
PHP_NEW_EXTENSION(opentelemetry, opentelemetry.c otel_observer.c, $ext_shared)

# 新
PHP_NEW_EXTENSION(phook, phook.c phook_observer.c, $ext_shared)
```

### 7. デフォルトハンドラーの実装

OpenTelemetry固有のハンドラーを置き換えるシンプルなデフォルトハンドラーを作成します:

```php
<?php
namespace PHook;

class DefaultHandler {
    public static function pre($instance, array $params, ?string $class, string $function, 
                             ?string $filename, ?int $lineno, ?array $args = null) {
        // デフォルトの実行前フック動作（OpenTelemetry機能なし）
        // 必要に応じて関数呼び出しをログに記録
        return null;
    }
    
    public static function post($instance, array $params, $returnValue) {
        // デフォルトの実行後フック動作（OpenTelemetry機能なし）
        return $returnValue;
    }
}
```

### 8. モジュール情報の更新

ファイル: `phook.c`

```c
PHP_MINFO_FUNCTION(phook)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "PHookサポート", "有効");
    php_info_print_table_row(2, "バージョン", PHP_PHOOK_VERSION);
    php_info_print_table_end();

    DISPLAY_INI_ENTRIES();
}
```

## 作成/変更するファイル

| 元のファイル | 新しいファイル | 必要な変更 |
|---------------|----------|------------------|
| `php_opentelemetry.h` | `php_phook.h` | モジュール名、マクロ、バージョンの更新 |
| `opentelemetry.c` | `phook.c` | モジュール名、関数名の変更、OpenTelemetryコードの削除 |
| `otel_observer.c` | `phook_observer.c` | 関数名の変更、グローバル参照の更新 |
| `otel_observer.h` | `phook_observer.h` | インクルードガード、関数宣言の更新 |
| `opentelemetry.stub.php` | `phook.stub.php` | 名前空間と関数説明の更新 |
| `opentelemetry_arginfo.h` | `phook_arginfo.h` | 更新されたスタブファイルから再生成 |
| `config.m4` | `config.m4` | 拡張機能名とヘルプテキストの更新 |
| `config.w32` | `config.w32` | Windows向けビルド用の拡張機能名の更新 |
| `build.sh` | `build.sh` | 拡張機能名の参照の更新 |

## 変更した拡張機能のテスト

これらの変更を行った後、拡張機能が正しく動作することを確認するためにテストする必要があります:

1. 拡張機能をビルド:
   ```
   phpize
   ./configure
   make
   ```

2. 拡張機能をインストール:
   ```
   make install
   ```

3. php.iniに拡張機能を追加:
   ```
   extension=phook
   ```

4. 次のことを検証するシンプルなテストスクリプトを作成:
   - 基本的な関数フックが動作する
   - メソッドフックが動作する
   - パラメータの変更が動作する
   - 戻り値の変更が動作する
   - 属性ベースのフックが動作する

## まとめ

この実装ガイドは、OpenTelemetry拡張機能をPHookというスタンドアロンのフックメカニズムに変換するための主要な手順を提供しています。結果として得られる拡張機能は、元の拡張機能の強力な関数/メソッド傍受機能を提供しますが、OpenTelemetry固有の機能は含まれません。