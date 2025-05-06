# 移行ガイド: OpenTelemetry拡張機能からPHookへ

このガイドでは、元のOpenTelemetry拡張機能から、関数フック機能だけに焦点を当てたスタンドアロンのPHook拡張機能への移行に必要な変更について説明します。

## 名称変更

### 名前空間の変更

| 元の名前 | 新しい名前 |
|----------|-----|
| `OpenTelemetry\Instrumentation` | `PHook` |

### 関数名の変更

| 元の名前 | 新しい名前 |
|----------|-----|
| `OpenTelemetry\Instrumentation\hook()` | `PHook\hook()` |

### 属性の変更

| 元の名前 | 新しい名前 |
|----------|-----|
| `#[WithSpan]` | `#[Hook]` |
| `#[SpanAttribute]` | `#[CaptureParam]` |

## 設定の変更

| 元のINI設定 | 新しいINI設定 |
|----------------------|-----------------|
| `opentelemetry.validate_hook_functions` | `phook.validate_hook_functions` |
| `opentelemetry.display_warnings` | `phook.display_warnings` |
| `opentelemetry.allow_stack_extension` | `phook.allow_stack_extension` |
| `opentelemetry.attr_hooks_enabled` | `phook.attr_hooks_enabled` |
| `opentelemetry.attr_pre_handler_function` | `phook.attr_pre_handler_function` |
| `opentelemetry.attr_post_handler_function` | `phook.attr_post_handler_function` |

## 削除された機能

以下のOpenTelemetry固有の機能が削除されました:

1. **OpenTelemetry SDKとの統合**
   - トレースやスパンの自動作成なし
   - OpenTelemetryコレクターとの統合なし

2. **スパン固有の動作**
   - `#[WithSpan]`属性を持つ関数の自動スパン作成なし
   - スパン属性としての自動パラメータキャプチャなし

3. **デフォルトハンドラー**
   - デフォルトの属性ハンドラーはもはやスパンを作成しない
   - 新しいデフォルトハンドラーは一般的なロギングと計装に焦点を当てる

## 必要な実装変更

### 1. 拡張機能の読み込み更新

```php
// 旧
extension=opentelemetry

// 新
extension=phook
```

### 2. 関数呼び出しの更新

```php
// 旧
OpenTelemetry\Instrumentation\hook(null, 'my_function', $pre, $post);

// 新
PHook\hook(null, 'my_function', $pre, $post);
```

### 3. 属性の更新

```php
// 旧
#[WithSpan]
function tracedFunction() {}

// 新
#[Hook]
function tracedFunction() {}

// 旧
function processUser(#[SpanAttribute] string $username) {}

// 新
function processUser(#[CaptureParam] string $username) {}
```

### 4. カスタムハンドラーの更新

`WithSpan`属性用のカスタムハンドラーを使用していた場合は、新しい形式に更新します:

```php
// 旧
opentelemetry.attr_pre_handler_function = "OpenTelemetry\API\Instrumentation\WithSpanHandler::pre"
opentelemetry.attr_post_handler_function = "OpenTelemetry\API\Instrumentation\WithSpanHandler::post"

// 新
phook.attr_pre_handler_function = "MyApp\Handlers\HookHandler::pre"
phook.attr_post_handler_function = "MyApp\Handlers\HookHandler::post"
```

### 5. カスタムトレースの実装（必要な場合）

OpenTelemetryトレースが必要な場合は、フックシステムを使って実装できます:

```php
// スパンを作成するフックを登録
PHook\hook(
    null,
    'important_function',
    function($instance, array $params) {
        $tracer = YourOpenTelemetryTracer::get();
        $span = $tracer->startSpan('important_function');
        // スパンをコンテキストまたはレジストリに保存
        YourSpanRegistry::setCurrent($span);
        return null;
    },
    function($instance, array $params, $returnValue) {
        // 現在のスパンを取得
        $span = YourSpanRegistry::getCurrent();
        $span->end();
        return $returnValue;
    }
);
```

## ソースコードの修正

これらの変更を拡張機能のソースコードに実装するには、以下のファイルを修正する必要があります:

1. **拡張機能の登録**
   - `php_opentelemetry.h`をOpenTelemetryの参照を削除するように更新
   - モジュールエントリで拡張機能名を更新

2. **フック実装**
   - `opentelemetry.c`を新しい名前空間と関数名を使用するように更新
   - OpenTelemetry固有のコードを削除

3. **オブザーバー実装**
   - `otel_observer.c`を新しい命名規則を使用するように更新
   - スパン固有のロジックを削除

4. **属性処理**
   - 属性クラスの名前を変更
   - 属性処理ロジックをOpenTelemetryに依存しないように更新

5. **設定**
   - INI設定名を新しいプレフィックスで更新

修正された拡張機能は、OpenTelemetry固有の機能なしで同じ強力なフック機能を提供します。